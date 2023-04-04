<?php

namespace App\Http\Controllers;

use App\Enums\ClientPlatform;
use App\Enums\UserAssetType;
use App\Http\ResponseHelpers\WalletTransactionsResponse;
use App\Models\WalletTransaction;
use App\Models\Plan;
use App\Models\UserPoint;
use App\Models\User;
use App\Models\Boost;
use App\Models\Profile;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Yabacon\Paystack\Event as PaystackEvent;
use Yabacon\Paystack;
use Yabacon\Paystack\Exception\ApiException as PaystackException;
use App\Enums\AchievementType;

use Illuminate\Support\Facades\Event;
use App\Events\AchievementBadgeEvent;

class WalletController extends BaseController
{

    public function me()
    {
        $data = [
            'wallet' => $this->user->wallet
        ];
        return $this->sendResponse($data, 'User wallet details');
    }

    public function transactions()
    {
        $transactions = $this->user->transactions()
            ->select('wallet_transactions.id as id', 'transaction_type as type', 'amount', 'description', 'wallet_transactions.created_at as transactionDate')
            ->orderBy('wallet_transactions.created_at', 'desc')
            ->paginate(10);

        return (new WalletTransactionsResponse())->transform($transactions);
    }

    public function earnings()
    {
        $data = [
            'earnings' => $this->user->transactions()
                ->where('transaction_type', 'CREDIT')
                ->where('description', '!=', 'Fund Wallet')
                ->where('description', '!=', 'Sign Up Bonus')
                ->orderBy('created_at', 'desc')->get()
        ];
        return $this->sendResponse($data, 'Earnings information');
    }

    public function verifyTransaction(string $reference)
    {
        Log::info("payment successful from app verification $this->user->username");

        return $this->sendResponse(true, 'Payment was successful');
    }

    public function paymentEventProcessor(Request $request)
    {
        if (!in_array($request->getClientIp(), ['52.31.139.75', '52.49.173.169', '52.214.14.220'])) {
            return response("", 200);
        }

        $event = PaystackEvent::capture();

        Log::info("in paystaaack");
        // Log::info("event from paystack ", $event->raw);

        $myKeys = [
            'key' => config('trivia.payment_key'),
        ];

        $owner = $event->discoverOwner($myKeys);

        if (!$owner) {
            Log::info("paystack call made with invalid key");
            return response("", 200);
        }

        Log::info(json_decode(json_encode($event), true));

        $reference = $event->obj->data->reference;
        $amount = $event->obj->data->amount;
        $status = $event->obj->data->status;

        switch ($event->obj->event) {

            case 'charge.success':
                $email = $event->obj->data->customer->email;
                if ('success' === $status) {
                    Log::info("successfull charge");
                    $isValidTransaction = $this->verifyPaystackTransaction($reference);

                    if ($isValidTransaction) {
                        Log::info("savuing funding transaction");
                        $this->savePaymentTransaction($reference, $email, $amount);
                    }
                }
                break;
            case 'transfer.success':
                Log::info("transfer successfull");
                return response("", 200);

            case 'transfer.reversed' || 'transfer.failed':
                Log::info("transfer failed or reversed");
                if ('reversed' === $status || 'failed' === $status) {
                    $isValidTransaction = $this->verifyPaystackTransaction($event->obj->data->reference);
                    if ($isValidTransaction) {
                        $this->reverseWithdrawalTransaction($reference, $amount);
                    }
                }
                break;
            default:
                break;
        }
    }

    private function verifyPaystackTransaction(string $reference)
    {
        // initiate the Library's Paystack Object
        $paystack = new Paystack(config('trivia.payment_key'));
        try {
            // verify using the library
            $tranx = $paystack->transaction->verify([
                'reference' => $reference, // unique to transactions
            ]);
        } catch (PaystackException $e) {
            Log::info("transaction could not be verified ", $e->getMessage());
            return false;
        }

        if ('success' == $tranx->data->status) {
            return true;
        }
        return false;
    }

    public function paymentsTransactionsReconciler(Request $request)
    {
        $client = new Client();
        $url = null;
        if ($request->has(['startDate', 'endDate'])) {
            $_startDate = Carbon::parse($request->startDate)->startOfDay()->toISOString();
            $_endDate = Carbon::parse($request->endDate)->tomorrow()->toISOString();
            $url = "https://api.paystack.co/transaction?status=success&from=$_startDate&to=$_endDate";

            Log::info("url with dates : $url");
        } else {
            $url = 'https://api.paystack.co/transaction?status=success';
            Log::info("url with no dates : $url");
        }
        $response = null;
        Log::info("about to fetch transactions from paystack ");
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' .  config('trivia.payment_key')
                ]
            ]);
        } catch (\Exception $ex) {
            Log::info("Something went wrong, could not fetch transactions");
            return $this->sendResponse(false, 'Transactions could not be fetched.');
        }
        $result = \json_decode((string) $response->getBody());
        Log::info("transactions fetched ", $result->data);

        foreach ($result->data as $data) {
            $existingReference = WalletTransaction::where('reference', $data->reference)->first();

            if ($existingReference == null) {
                Log::info("successful transaction reference: $data->reference with no record found, inserting... ");
                $this->savePaymentTransaction($data->reference, $data->customer->email, $data->amount);
            }
        }
        Log::info("Records reconciled ");
        return $this->sendResponse(true, 'Transactions reconciled');
    }

    private function savePaymentTransaction($reference, $email, $amount)
    {
        if (!is_null(WalletTransaction::where('reference', $reference)->first())) {
            Log::info('payment transaction already exists');
            return response("", 200);
        }

        $user = User::where('email', $email)->first();
        $user->wallet->non_withdrawable_balance += ($amount) / 100;

        WalletTransaction::create([
            'wallet_id' => $user->wallet->id,
            'transaction_type' => 'CREDIT',
            'amount' => ($amount) / 100,
            'balance' => $user->wallet->non_withdrawable_balance,
            'description' => 'Fund Wallet',
            'reference' => $reference,
        ]);
        $user->wallet->save();

        Log::info('payment successful from paystack');
        return response("", 200);
    }

    private function reverseWithdrawalTransaction($reference, $amount)
    {
        $transaction = WalletTransaction::where('reference', $reference)->first();

        if (is_null($transaction)) {
            Log::info('trying to reverse non existent transaction');
            return response("", 200);
        }

        $transaction->wallet->withdrawable_balance += ($amount) / 100;

        WalletTransaction::create([
            'wallet_id' =>  $transaction->wallet_id,
            'transaction_type' => 'CREDIT',
            'amount' => ($amount) / 100,
            'balance' => $transaction->wallet->withdrawable_balance,
            'description' => 'Winnings Withdrawal Reversed',
            'reference' => $reference,
        ]);
        $transaction->wallet->save();

        Log::info('withdrawal reversed for transaction reference ' . $reference);
        return response("", 200);
    }


    public function getBanks()
    {
        $result = cache('banks');
        if ($result) {
            return response()->json($result, 200);
        }

        $client = new Client();
        $url = 'https://api.paystack.co/bank';
        $response = null;
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' .  config('trivia.payment_key')
                ]
            ]);
        } catch (\Exception $ex) {
            return $this->_failedPaymentVerification();
        }

        $result = \json_decode((string) $response->getBody());
        Cache::forever('banks', $result);

        return response()->json($result, 200);
    }

    //when a user chooses to buy boost with points
    public function buyBoostsWithPoints($boostId)
    {

        $boost = Boost::find($boostId);

        if ($boost == null) {
            return $this->sendError([], 'Wrong boost selected');
        }

        $points = $this->user->points();
        if ($points < ($boost->point_value)) {
            return $this->sendError(false, 'You do not have enough points');
        }

        //log point traffic
        UserPoint::create([
            'user_id' => $this->user->id,
            'value' => $boost->point_value,
            'description' => 'Points used for buying ' . $boost->name . ' boosts',
            'point_flow_type' => 'POINTS_SUBTRACTED',
        ]);

        //credit user with bought boost
        //if user already has boost, add to boost else create new boost for user
        $userBoost = $this->user->boosts()->where('boost_id', $boostId)->first();
        if ($userBoost === null) {
            $this->user->boosts()->create([
                'user_id' => $this->user->id,
                'boost_id' => $boostId,
                'boost_count' => $boost->pack_count,
                'used_count' => 0
            ]);
        } else {
            $userBoost->update(['boost_count' => $userBoost->boost_count + $boost->pack_count]);
        }

        // trigger event for achievement
        Event::dispatch(new AchievementBadgeEvent($this->user, AchievementType::BOOST_BOUGHT, $boost));

        return $this->sendResponse($points - $boost->point_value, 'Boost Bought');
    }

    //when a user chooses to buy boost from wallet
    public function buyBoostsFromWallet($boostId)
    {
        $boost = Boost::find($boostId);

        if ($boost == null) {
            return $this->sendError([], 'Wrong boost selected');
        }

        $wallet = $this->user->wallet;
        if ($wallet->non_withdrawable_balance < ($boost->currency_value)) {
            return $this->sendError([], 'You do not have enough money in your wallet.');
        }

        $wallet->non_withdrawable_balance -= $boost->currency_value;
        $wallet->save();


        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'transaction_type' => 'DEBIT',
            'amount' => $boost->currency_value,
            'balance' => $wallet->non_withdrawable_balance,
            'description' => 'Bought ' . strtoupper($boost->name) . ' boosts',
            'reference' => Str::random(10),
        ]);

        $userBoost = $this->user->boosts()->where('boost_id', $boostId)->first();

        if ($userBoost == null) {
            $this->user->boosts()->create([
                'user_id' => $this->user->id,
                'boost_id' => $boostId,
                'boost_count' => $boost->pack_count,
                'used_count' => 0
            ]);
        } else {
            $userBoost->update(['boost_count' => $userBoost->boost_count + $boost->pack_count]);
        }

        // trigger event for achievement
        Event::dispatch(new AchievementBadgeEvent($this->user, AchievementType::BOOST_BOUGHT, $boost));

        return $this->sendResponse($wallet->non_withdrawable_balance, 'Boost Bought');
    }


    private function _failedPaymentVerification()
    {
        return $this->sendResponse(false, 'Payment could not be verified. Please wait for your balance to reflect.');
    }

    public function subscribeToPlan($planId)
    {
        $plan = Plan::find($planId);

        if ($plan === null) {
            return $this->sendError('Plan does not exist', 'Plan does not exist');
        }

        if ($plan->price > $this->user->wallet->non_withdrawable_balance) {
            return $this->sendError('Your wallet balance cannot afford this plan', 'Your wallet balance cannot afford this plan');
        }

        $this->user->wallet->non_withdrawable_balance -= $plan->price;
        $this->user->wallet->save();

        WalletTransaction::create([
            'wallet_id' => $this->user->wallet->id,
            'transaction_type' => 'DEBIT',
            'amount' => $plan->price,
            'balance' => $this->user->wallet->non_withdrawable_balance,
            'description' => 'BOUGHT ' . $plan->game_count . ' GAMES',
            'reference' => Str::random(10),
        ]);


        DB::table('user_plans')->insert([
            'user_id' => $this->user->id,
            'plan_id' => $plan->id,
            'description' => 'BOUGHT ' . $plan->game_count . ' GAMES',
            'is_active' => true,
            'used_count' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        // trigger event for achievement
        Event::dispatch(new AchievementBadgeEvent($this->user, AchievementType::GAME_BOUGHT, $plan));

        return $this->sendResponse(
            'You have successfully bought ' . $plan->game_count . ' games',
            'You have successfully bought ' . $plan->game_count . ' games'
        );
    }

    public function itemPurchased(Request $request, ClientPlatform $clientPlatform)
    {
        if($clientPlatform != ClientPlatform::GameArkMobile){
            return $this->sendError('App type not supported', 'App type not supported');
        }
        $data = $request->validate([
            'type' => ['required'],
            'item_id' => ['required']
        ]);

        $type = null;

        switch ($data['type']) {
            case 'BOOST':
                $type = UserAssetType::BOOST;
                break;

            default:
                # code...
                $type = UserAssetType::PLAN;
                break;
        }

        if ($type == UserAssetType::BOOST) {
            $boostId = $data['item_id'];
            $boost = Boost::find($boostId);

            if ($boost == null) {
                return $this->sendError([], 'Wrong boost selected');
            }

            $userBoost = $this->user->boosts()->where('boost_id', $boostId)->first();

            if ($userBoost == null) {
                $this->user->boosts()->create([
                    'user_id' => $this->user->id,
                    'boost_id' => $boostId,
                    'boost_count' => $boost->pack_count,
                    'used_count' => 0
                ]);
            } else {
                $userBoost->update(['boost_count' => $userBoost->boost_count + $boost->pack_count]);
            }

            // trigger event for achievement
            Event::dispatch(new AchievementBadgeEvent($this->user, AchievementType::BOOST_BOUGHT, $boost));

        }else{
            $planId = $data['item_id'];

            $plan = Plan::find($planId);
            if ($plan === null) {
                return $this->sendError('Plan does not exist', 'Plan does not exist');
            }

            DB::table('user_plans')->insert([
                'user_id' => $this->user->id,
                'plan_id' => $plan->id,
                'description' => 'BOUGHT ' . $plan->game_count . ' GAMES',
                'is_active' => true,
                'used_count' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            // trigger event for achievement
            Event::dispatch(new AchievementBadgeEvent($this->user, AchievementType::GAME_BOUGHT, $plan));

            return response("Item purchased", 200);
        }


    }
}

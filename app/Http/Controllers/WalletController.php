<?php

namespace App\Http\Controllers;

use App\Models\WalletTransaction;
use App\Models\Plan;
use App\Models\UserPoint;
use App\Models\UserPlan;
use App\Models\Boost;
use GuzzleHttp\Client;
use stdClass;
use Illuminate\Support\Facades\Mail;
use App\Mail\WithdrawalRequest;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;


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
            ->select('transaction_type as type', 'amount', 'description', 'wallet_transactions.created_at as transactionDate')
            ->get();

        return $this->sendResponse($transactions, 'Wallet transactions information');
    }

    //this will be modified to appropriately return user earnings after a tournament
    //since wallet has been modified, and tournament mode is yet to be developed, A user has no earnings yet
    public function earnings()
    {
        $data = [
            'earnings' => $this->user->transactions()
                ->where('transaction_type', 'Fund Recieved')
                ->orderBy('created_at', 'desc')->get()
        ];
        return $this->sendResponse($data, 'Earnings information');
    }

    public function verifyTransaction(string $reference)
    {
        $client = new Client();
        $url = 'https://api.paystack.co/transaction/verify/' . $reference;
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
        if (!$result->status) {
            return $this->_failedPaymentVerification();
        }

        $wallet = $this->user->wallet;

        //#paystack returns in kobo hence divide by 100 for naira
        $value = ($result->data->amount / 100);
        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'transaction_type' => 'CREDIT',
            'amount' => $value,
            'description' => 'FUND WALLET FROM BANK',
            'reference' => $result->data->reference,
        ]);

        $wallet->balance += $value;
        $wallet->save();

        // $this->creditPoints($this->user->id, ($value * 5 / 100), "5% cashback for funding wallet");

        return $this->sendResponse(true, 'Payment was successful');
    }

    public function getBanks()
    {
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
        if ($wallet->balance < ($boost->currency_value)) {
            return $this->sendError([], 'You do not have enough money in your wallet.');
        }

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'transaction_type' => 'DEBIT',
            'amount' => $boost->currency_value,
            'description' => 'BOUGHT ' . strtoupper($boost->name) . ' BOOSTS FROM WALLET',
            'reference' => Str::random(10),
        ]);

        $wallet->balance -= $boost->currency_value;
        $wallet->save();

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

        return $this->sendResponse($wallet->balance, 'Boost Bought');
    }


    private function _failedPaymentVerification()
    {
        return $this->sendResponse(false, 'Payment could not be verified. Please wait for your balance to reflect.');
    }

    public function subscribeToPlan($planId){
        $plan = Plan::find($planId);
        
        if($plan === null){
            return $this->sendError('Plan does not exist', 'Plan does not exist');
        }

        if($plan->price > $this->user->wallet->balance){
            return $this->sendError('Your wallet balance cannot afford this plan', 'Your wallet balance cannot afford this plan');
        }

        $userPlans = UserPlan::where('user_id', $this->user->id)->where('is_active',true)->get();
        
        foreach($userPlans as $u_p){
            $p = Plan::where('id',$u_p->plan_id)->first();
            
            if($p->is_free === false){
                return $this->sendError('You already have an active paid plan.', 'You already have an active paid plan.');
            }
        }
        
        $this->user->wallet->balance -= $plan->price;
    
        WalletTransaction::create([
            'wallet_id' => $this->user->wallet->id,
            'transaction_type' => 'DEBIT',
            'amount' => $plan->price,
            'description' => 'SUBSCRIBED TO ' . strtoupper($plan->name) ,
            'reference' => Str::random(10),
        ]);

        $this->user->wallet->save();

        DB::table('user_plans')->insert([
            'user_id' => $this->user->id,
            'plan_id' => $plan->id,
            'is_active'=> true,
            'used_count'=> 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        return $this->sendResponse('You have successfully subscribed to '.$plan->name .' plan',
             'You have successfully subscribed to '.$plan->name .' plan');   
       
    }
}

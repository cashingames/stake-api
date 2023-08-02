<?php

namespace App\Http\Controllers;

use App\Actions\Boosts\BuyBoostAction;
use App\Enums\WalletTransactionAction;
use App\Enums\WalletTransactionType;
use App\Models\WalletTransaction;
use App\Models\User;
use App\Models\Boost;
use App\Repositories\Cashingames\WalletTransactionDto;
use Yabacon\Paystack;
use App\Enums\WalletBalanceType;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Yabacon\Paystack\Event as PaystackEvent;
use App\Repositories\Cashingames\WalletRepository;
use Yabacon\Paystack\Exception\ApiException as PaystackException;
use App\Http\ResponseHelpers\WalletTransactionsResponse;
use App\Repositories\Cashingames\BoostRepository;

class WalletController extends BaseController
{

    public function __construct(
        private readonly BoostRepository $boostRepository,
        private readonly WalletRepository $walletRepository,
        private readonly BuyBoostAction $buyBoostAction,
    ) {
        parent::__construct();
    }
    public function me()
    {
        $data = [
            'wallet' => $this->user->wallet
        ];
        return $this->sendResponse($data, 'User wallet details');
    }

    public function transactions()
    {
        $mainTransactions = $this->user->wallet->transactions()->mainTransactions()
            ->select('wallet_transactions.id as id', 'transaction_type as type', 'amount', 'description', 'wallet_transactions.created_at as transactionDate')
            ->orderBy('wallet_transactions.created_at', 'desc')
            ->paginate(10);

        $bonusTransactions = $this->user->wallet->transactions()->bonusTransactions()
            ->select('wallet_transactions.id as id', 'transaction_type as type', 'amount', 'description', 'wallet_transactions.created_at as transactionDate')
            ->orderBy('wallet_transactions.created_at', 'desc')
            ->paginate(10);

        $winningsTransactions = $this->user->wallet->transactions()->winningsTransactions()
            ->select('wallet_transactions.id as id', 'transaction_type as type', 'amount', 'description', 'wallet_transactions.created_at as transactionDate')
            ->orderBy('wallet_transactions.created_at', 'desc')
            ->paginate(10);

        $data = [
            "mainTransactions" => (new WalletTransactionsResponse())->transform($mainTransactions)->original,
            "bonusTransactions" => (new WalletTransactionsResponse())->transform($bonusTransactions)->original,
            "withdrawalsTransactions" => (new WalletTransactionsResponse())->transform($winningsTransactions)->original
        ];
        return $data;
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
                if ('success' == $status) {
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
                $email = $event->obj->data->recipient->email;
                if ('reversed' == $status || 'failed' == $status) {
                    $isValidTransaction = $this->verifyPaystackTransaction($event->obj->data->reference);
                    if ($isValidTransaction) {
                        $this->reverseWithdrawalTransaction($reference, $amount, $email);
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
            Log::info("transaction could not be verified . Error : " . $e->getMessage());
            return false;
        }

        if ('success' == $tranx->data->status) {
            return true;
        }
        return false;
    }
    public function savePaymentTransaction($reference, $email, $amount)
    {
        $transaction = WalletTransaction::where('reference', $reference)->sharedLock()->first();
        if (!is_null($transaction)) {
            Log::info('payment transaction already exists');
            return response("", 200);
        }

        $user = User::where('email', $email)->first();

        $hasFundedBefore = $this->walletRepository->hasFundedBefore($user);
        if (!$hasFundedBefore) {
            $bonusAmount = ($amount / 100) * (config('trivia.bonus.signup.registration_bonus_percentage') / 100);
            if ($bonusAmount > config('trivia.bonus.signup.registration_bonus_limit')) {
                $bonusAmount = config('trivia.bonus.signup.registration_bonus_limit');
            }
            $this->walletRepository->addTransaction(
                new WalletTransactionDto(
                    $user->id,
                    $bonusAmount,
                    '100% welcome bonus',
                    WalletBalanceType::BonusBalance,
                    WalletTransactionType::Credit,
                    WalletTransactionAction::BonusCredited
                )
            );
        }

        $this->walletRepository->addTransaction(
            new WalletTransactionDto(
                $user->id,
                ($amount / 100),
                'Wallet Top-up',
                WalletBalanceType::CreditsBalance,
                WalletTransactionType::Credit,
                WalletTransactionAction::WalletFunded
            )
        );

        Log::info('payment successful from paystack');
        return response("", 200);
    }

    private function reverseWithdrawalTransaction($reference, $amount, $email)
    {
        $transaction = WalletTransaction::where('reference', $reference)->first();
        $user = User::where('email', $email)->first();

        if (is_null($transaction)) {
            Log::info('trying to reverse non existent transaction');
            return response("", 200);
        }

        $this->walletRepository->addTransaction(
            new WalletTransactionDto(
                $user->id,
                ($amount / 100),
                'Failed Withdrawal Reversed',
                WalletBalanceType::WinningsBalance,
                WalletTransactionType::Credit,
                WalletTransactionAction::FundsReversed
            )
        );

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
                    'Authorization' => 'Bearer ' . config('trivia.payment_key')
                ]
            ]);
        } catch (\Exception $ex) {
            return $this->_failedPaymentVerification();
        }

        $result = \json_decode((string) $response->getBody());
        Cache::forever('banks', $result);

        return response()->json($result, 200);
    }

    //when a user chooses to buy boost from wallet
    public function buyBoostsFromWallet($boostId)
    {
        $boost = Boost::find($boostId);

        if ($boost == null) {
            return $this->sendError([], 'Wrong boost selected');
        }

        $wallet = $this->user->wallet;
        if ($wallet->non_withdrawable < ($boost->price)) {
            return $this->sendError([], 'You do not have enough money in your wallet.');
        }

        $this->buyBoostAction->execute($boostId, WalletBalanceType::CreditsBalance);

        return $this->sendResponse($wallet->non_withdrawable, 'Boost Bought');
    }

    private function _failedPaymentVerification()
    {
        return $this->sendResponse(false, 'Payment could not be verified. Please wait for your balance to reflect.');
    }
}

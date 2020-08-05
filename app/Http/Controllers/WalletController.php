<?php

namespace App\Http\Controllers;

use App\WalletTransaction;
use GuzzleHttp\Client;
use stdClass;
use Illuminate\Support\Facades\Mail;
use App\Mail\WithdrawalRequest;
use Illuminate\Support\Str;

class WalletController extends BaseController
{

    public function me()
    {
        $data = [
            'wallet' => auth()->user()->wallet
        ];
        return $this->sendResponse($data, 'User wallet details');
    }

    public function transactions()
    {
        $data = [
            'transactions' => auth()->user()->transactions()->orderBy('created_at', 'desc')->get()
        ];
        return $this->sendResponse($data, 'Wallet transactions information');
    }

    public function verifyTransaction(string $reference)
    {

        $client = new Client();
        $url = 'https://api.paystack.co/transaction/verify/' . $reference;
        $response = null;
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' .  config('app.payment_key')
                ]
            ]);
        } catch (\Exception $ex) {
            return $this->_failedPaymentVerification();
        }

        $result = \json_decode((string) $response->getBody());
        if (!$result->status) {
            return $this->_failedPaymentVerification();
        }

        $wallet = auth()->user()->wallet;
        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'transaction_type' => 'CREDIT',
            'amount' => ($result->data->amount / 100),
            'wallet_type' => 'CASH',
            'description' => 'Fund wallet cash balance',
            'reference' => $result->data->reference,
        ]);
        return $this->sendResponse(true, 'Payment was successful');
    }

    public function getBanks(){
        $client = new Client();
        $url = 'https://api.paystack.co/bank';
        $response = null;
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' .  config('app.payment_key')
                ]
            ]);
        } catch (\Exception $ex) {
            return $this->_failedPaymentVerification();
        }

        $result = \json_decode((string) $response->getBody());
        // $result->map(function($x){
        //     $y = new stdClass;
        //     $y->
        // });
        return response()->json($result, 200);

    }

    public function withdrawRequest($bankName,$accountName,$accountNumber,$amount){
        Mail::send(new WithdrawalRequest($bankName,$accountName,$accountNumber,$amount));

        $user = auth()->user();
        $wallet = auth()->user()->wallet;
        // echo($wallet->cash);
        // echo($amount);
        // echo($wallet->cash - $amount);
        // die();
        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'transaction_type' => 'DEBIT',
            'amount' => $amount,
            'wallet_type' => 'CASH',
            'description' => 'Cash Withdrawal',
            'reference' => Str::random(10),
        ]);

        $user->wallet->refresh();
        // echo($user);
        // die();

        return $this->sendResponse($wallet, 'Withrawal Request sent.');

    }

    private function _failedPaymentVerification()
    {
        return $this->sendResponse(false, 'Payment could not be verified. Please wait for your balance to reflect.');
    }
}

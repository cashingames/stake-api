<?php

namespace App\Http\Controllers;

use App\Models\WalletTransaction;
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
            'wallet' => $this->user->wallet
        ];
        return $this->sendResponse($data, 'User wallet details');
    }

    public function transactions()
    {
        $data = [
            'transactions' => $this->user->transactions()->orderBy('created_at', 'desc')->get()
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
        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'transaction_type' => 'CREDIT',
            'amount' => ($result->data->amount / 100),
            'wallet_kind' => 'CREDITS',
            'description' => 'FUND WALLET FROM BANK',
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
                    'Authorization' => 'Bearer ' .  config('trivia.payment_key')
                ]
            ]);
        } catch (\Exception $ex) {
            return $this->_failedPaymentVerification();
        }

        $result = \json_decode((string) $response->getBody());
        return response()->json($result, 200);

    }

    public function withdrawRequest(Request $request){

        $data = $request->validate([
            'bankName' => ['required', 'string', 'max:20'],
            'accountName' => ['required', 'string', 'max:20'],
            'accountNumber' => ['nullable', 'string', 'max:20'],
            'amount' => ['required', 'string', 'max:20'],
        ]);

        Mail::send(new WithdrawalRequest($data['bankName'],$data['accountName'],$data['accountNumber'],$data['amount']));

        $wallet = $this->user->wallet;
        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'transaction_type' => 'DEBIT',
            'amount' => $amount,
            'wallet_kind' => 'WINNINGS',
            'description' => 'WITHDRAW TO BANK',
            'reference' => Str::random(10),
        ]);
        
        $wallet->refresh();
        return $this->sendResponse($wallet, 'Withrawal Request sent.');

    }

    private function _failedPaymentVerification()
    {
        return $this->sendResponse(false, 'Payment could not be verified. Please wait for your balance to reflect.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use GuzzleHttp\Client;
use stdClass;
use Illuminate\Support\Facades\Mail;
use App\Mail\WithdrawalRequest;
use Illuminate\Support\Str;
use Illuminate\Http\Request;


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

    // public function earnings()
    // {
    //     $data = [
    //         'earnings' => $this->user->transactions()
    //         ->where('transaction_type', 'Fund Recieved')
    //         ->where('wallet_kind', 'WINNINGS')
    //         ->orderBy('created_at', 'desc')->get()
    //     ];
    //     return $this->sendResponse($data, 'Earnings information');
    // }

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
            'transaction_type' => 'Fund Recieved',
            'amount' => ($result->data->amount / 100),
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

    public function buyBoostsWithPoints($boostId){

        $boost = Boost::find($boostId);
        
        if ($boost==null){
           return $this->sendResponse('Wrong boost selected', 'Wrong boost selected');
        }

        $points = $this->user->points;

        if($points >= $boost->point_value){
            
           $points -= $boost->point_value;

            $this->user->boosts()->create([
                'user_id' => $this->user->id,
                'boost_id' => $boost->Id,
                'boost_count'=> $boost->pack_count,
                'used_count'=>0
            ]);

        return $this->sendResponse(true, 'Boost Bought');
        } 
        return $this->sendResponse(false, 'You do not have enough points');
    }

    public function buyBoostsFromWallet($boostId){
        $boost = Boost::find($boostId);
        
        if ($boost==null){
           return $this->sendResponse('Wrong boost selected', 'Wrong boost selected');
        }

        $wallet = $this->user->wallet;

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'transaction_type' => 'Fund Withdrawal',
            'amount' => $boost->currency_value,
            'description' => 'BOUGHT BOOSTS',
            'reference' => $result->data->reference,
        ]);
        
        $this->user->boosts()->create([
            'user_id' => $this->user->id,
            'boost_id' => $boost->Id,
            'boost_count'=> $boost->pack_count,
            'used_count'=>0
        ]);

        return $this->sendResponse(true, 'Boost Bought');
    }

    // public function withdrawRequest(Request $request){

    //     $data = $request->validate([
    //         'bankName' => ['required', 'string', 'max:100'],
    //         'accountName' => ['required', 'string', 'max:100'],
    //         'accountNumber' => ['nullable', 'string', 'max:20'],
    //         'amount' => ['required', 'string', 'max:20'],
    //     ]);

    //     Mail::send(new WithdrawalRequest($data['bankName'],$data['accountName'],$data['accountNumber'],$data['amount']));

    //     $wallet = $this->user->wallet;
       
    //     WalletTransaction::create([
    //         'wallet_id' => $wallet->id,
    //         'transaction_type' => 'Fund Withdrawal',
    //         'amount' => $data['amount'],
    //         'wallet_kind' => 'WINNINGS',
    //         'description' => 'Withdraw to bank',
    //         'reference' => Str::random(10),
    //     ]);

    //     $user = auth()->user(); 
        
    //     Withdrawal::create([
    //         'user_id' => $user->id,
    //         'bank_name' => $data['bankName'],
    //         'amount' => $data['amount'],
    //         'account_name' => $data['accountName'],
    //         'account_number' => $data['accountNumber'],
    //         'status' => 'REQUEST_RECIEVED'
           
    //     ]);
        
    //     $wallet->refresh();
    //     return $this->sendResponse('Withrawal Request sent.', 'Withrawal Request sent.');

    // }

    private function _failedPaymentVerification()
    {
        return $this->sendResponse(false, 'Payment could not be verified. Please wait for your balance to reflect.');
    }

    // public function getWithdrawals(){
    //     return $this->sendResponse(Withdrawal::latest()->get(),"withdrawals");
    // }
}
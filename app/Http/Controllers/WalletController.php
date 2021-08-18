<?php

namespace App\Http\Controllers;

use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use App\Models\Boost;
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
        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'transaction_type' => 'CREDIT',
            'amount' => ($result->data->amount / 100),
            'description' => 'FUND WALLET FROM BANK',
            'reference' => $result->data->reference,
        ]);

        $wallet->balance += $result->data->amount / 100; 
        $wallet->save();

        $this->creditPoints($this->user->id,100,"Points credited for funding wallet");

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

    //when a user chooses to buy boost with points
    public function buyBoostsWithPoints($boostId){

        $boost = Boost::find($boostId);
        
        if ($boost==null){
           return $this->sendResponse('Wrong boost selected', 'Wrong boost selected');
        }

        $points = $this->user->points;
        
        if($points >= ($boost->point_value)){
            //subtract points from user
            $this->user->update(['points'=>$points - $boost->point_value ]);
           
            //log point traffic
            $this->user->points()->create([
                'user_id' => $this->user->id,
                'value' => $boost->point_value ,
                'description'=> 'Points used for buying boosts',
                'point_flow_type'=>'POINTS_SUBTRACTED',
            ]);
            
            //credit user with bought boost
                //if user already has boost, add to boost else create new boost for user
            $userBoost = $this->user->boosts()->where('boost_id',$boostId)->first();
            if($userBoost === null){
                $this->user->boosts()->create([
                    'user_id' => $this->user->id,
                    'boost_id' => $boostId,
                    'boost_count'=> $boost->pack_count,
                    'used_count'=>0
                ]);
            }
            else {
                $userBoost->update(['boost_count'=>$userBoost->boost_count+$boost->pack_count]);
            }

        return $this->sendResponse(true, 'Boost Bought');
        } 
        return $this->sendResponse(false, 'You do not have enough points');
    }

    //when a user chooses to buy boost from wallet
    public function buyBoostsFromWallet($boostId){
        $boost = Boost::find($boostId);
        
        if ($boost==null){
           return $this->sendResponse('Wrong boost selected', 'Wrong boost selected');
        }

        $wallet = $this->user->wallet;
        if($wallet->balance >=($boost->currency_value )){

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'transaction_type' => 'DEBIT',
                'amount' => $boost->currency_value ,
                'description' => 'BOUGHT BOOSTS FROM WALLET',
                'reference' => Str::random(10),
            ]);
            
            $wallet->balance -= $boost->currency_value ; 
            $wallet->save();

            $userBoost = $this->user->boosts()->where('boost_id',$boostId)->first();

            if($userBoost === null){
                $this->user->boosts()->create([
                    'user_id' => $this->user->id,
                    'boost_id' => $boostId,
                    'boost_count'=> $boost->pack_count,
                    'used_count'=>0
                ]);
            }
            else {
                $userBoost->update(['boost_count'=>$userBoost->boost_count+$boost->pack_count]);
            }
            
            return $this->sendResponse(true, 'Boost Bought');
        }
        return $this->sendResponse(false, 'You do not have enough money in your wallet.');
    }

    
    private function _failedPaymentVerification()
    {
        return $this->sendResponse(false, 'Payment could not be verified. Please wait for your balance to reflect.');
    }

    // public function getWithdrawals(){
    //     return $this->sendResponse(Withdrawal::latest()->get(),"withdrawals");
    // }
}
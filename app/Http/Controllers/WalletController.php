<?php

namespace App\Http\Controllers;

use App\WalletTransaction;
use GuzzleHttp\Client;

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
            'transactions' => auth()->user()->transactions
        ];
        return $this->sendResponse($data, 'Wallet transactions information');
    }

    public function verifyTransaction(string $reference)
    {

        $client = new Client();
        $url = 'https://api.paystack.co/transaction/verify/946778697' ;
        $response = null;
        try{

            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer sk_test_a0d0725ffd8124018859083fe04d77eeec41407f'
                ]
            ]);

        }catch(\Exception $ex){
            return $this->_failedPaymentVerification();
        }

        $result = \json_decode((string) $response->getBody());
        if(!$result->status){
            return $this->_failedPaymentVerification();
        }

        $wallet = auth()->user()->wallet;
        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'transaction_type' => 'CREDIT',
            'amount' => $result->data->amount,
            'wallet_type' => 'CASH',
            'description' => 'Game live purchase',
            'reference' => $result->data->reference,
        ]);
        return $this->sendResponse(true, 'Payment was successful');
    }

    private function _failedPaymentVerification(){
        return $this->sendResponse(false, 'Payment could not be verified. Please wait for your balance to reflect.');
    }
}

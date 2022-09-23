<?php

namespace App\Services\Payments;

use App\Models\WalletTransaction;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaystackWithdrawalService
{

    /**
     * Paystack Api key 
     */
    private $paystackKey, $client, $user;


    public function __construct(string $paystackKey)
    {
        $this->paystackKey = $paystackKey;
        $this->client = new Client([
            'headers' => [
                'Authorization' => 'Bearer ' .  $this->paystackKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);

        $this->user = auth()->user();
    }

    public function verifyAccount($bankCode)
    {

        $url = 'https://api.paystack.co/bank/resolve?account_number=' . $this->user->profile->account_number . '&bank_code=' . $bankCode;
        $response = null;
        try {
            $response = $this->client->request('GET', $url);
        } catch (\Exception $ex) {
            Log::info($ex);
            return false;
        }

        $data = \json_decode((string) $response->getBody());
        return $data->status;
    }

    public function createTransferRecipient($bankCode)
    {

        $url = "https://api.paystack.co/transferrecipient";
        $response = null;
        try {
            $response = $this->client->request("POST", $url, [
                'json' => [
                    "type" => "nuban",
                    "name" => $this->user->profile->account_name,
                    "account_number" => $this->user->profile->account_number,
                    "bank_code" => $bankCode,
                    "currency" => "NGN"
                ]
            ]);
        } catch (\Exception $ex) {
            Log::info($ex);
            return null;
        }
        $data = \json_decode((string) $response->getBody());
        return $data->data->recipient_code;
    }

    public function initiateTransfer($recipientCode, $amount)
    {

        $url = "https://api.paystack.co/transfer";
        $response = null;
        try {
            $response = $this->client->request("POST", $url, [
                'json' => [
                    'source' => "balance",
                    'amount' => $amount,
                    'recipient' => $recipientCode,
                    'reason' => "Winnings withdrawal made"
                ]
            ]);
        } catch (\Exception $ex) {
            Log::info($ex);  
            throw $ex;
        }
        $data = \json_decode((string) $response->getBody());
        Log::info("feedback from paystack: " . json_encode($data));

        return($data->data);
    }

    public function getBanks()
    {
        $url = 'https://api.paystack.co/bank';
        $response = null;
        try {
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' .  $this->paystackKey
                ]
            ]);
        } catch (\Exception $ex) {
            Log::info($ex);
        }

        $banks = \json_decode((string) $response->getBody());

        Cache::forever('banks', $banks);
        return Cache::get('banks');
    }
}

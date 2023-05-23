<?php

namespace App\Services\Payments;

use App\Models\WalletTransaction;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use stdClass;

class PaystackService
{
    private $paystackKey;
    private $client;
    private $user;

    public function __construct()
    {
        $this->paystackKey = config('trivia.payment_key');
        $this->client = new Client([
            'headers' => [
                'Authorization' => 'Bearer ' .  $this->paystackKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);

        $this->user = auth()->user();
    }

    public function verifyAccount($bankCode, $accountNumber)
    {
        $url = 'https://api.paystack.co/bank/resolve?account_number=' .
            $accountNumber . '&bank_code=' . $bankCode;
        $response = null;
        try {
            $response = $this->client->request('GET', $url);
        } catch (\Exception $ex) {
            Log::info($ex);
            $response = new stdClass;
            $response->status = false;
            return $response;
        }

        $data = \json_decode((string) $response->getBody());
        return $data;
    }

    public function createTransferRecipient($bankCode, $accountName, $accountNumber)
    {
        $url = "https://api.paystack.co/transferrecipient";
        $response = null;
        try {
            $response = $this->client->request("POST", $url, [
                'json' => [
                    "type" => "nuban",
                    "name" => $accountName,
                    "account_number" => $accountNumber,
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

        return $data->data;
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
        return $banks;
    }
}

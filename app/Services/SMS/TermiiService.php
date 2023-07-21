<?php

namespace App\Services\SMS;

use App\Models\AuthToken;
use GuzzleHttp\Client;
use App\Services\SMS\SMSProviderInterface;
use Illuminate\Support\Facades\Log;

/**
 * @codeCoverageIgnore
 */
class TermiiService implements SMSProviderInterface
{

    protected $baseUrl = "https://api.ng.termii.com";

    protected $apiKey;


    protected $networkClient;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->networkClient = new Client(
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'base_uri' => $this->baseUrl
            ]
        );
    }

    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Send message to recipient
     * 
     * @param $data array
     */
    public function send(array $data)
    {
        $data['api_key'] = $this->getApiKey();
        !isset($data['channel']) ? $data['channel'] = "dnd" : $data['channel'];
        !isset($data['type']) ? $data['type'] = "plain" : $data['type'];

        $response = $this->networkClient->request("POST", "/api/sms/send", ['json' => $data, 'verify' => false]);
        return json_decode($response->getBody());
    }

    public function deliverOTP($user, $tokenType)
    {
        $otp_token = mt_rand(10000, 99999);

        AuthToken::create([
            'user_id' => $user->id,
            'token' => $otp_token,
            'token_type' => $tokenType,
            'expire_at' => now()->addMinutes(config('auth.verification.minutes_before_otp_expiry'))->toDateTimeString()
        ]);

        $smsData = [
            'to' => $user->country_code . (substr($user->phone_number, -10)),
            'channel' => 'dnd',
            'type' => 'plain',
            'from' => "N-Alert",
            'sms' => "{$user->username}, your Cashingames secure OTP is {$otp_token}. Do not share with anyone"
        ];
        try {
            return $this->send($smsData);
        } catch (\Throwable $th) {
            Log::error("Unable to deliver OTP via SMS Reason: " . $th->getMessage());
        }

        return null;
    }
}

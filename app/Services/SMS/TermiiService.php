<?php

namespace App\Services\SMS;

use App\Jobs\ExpireGeneratedOtp;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use App\Services\SMS\SMSProviderInterface;

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

    private function generateOtp()
    {
        $otp = mt_rand(10000, 99999);

        $user = User::where('otp_token',  $otp);

        if ($user->exists()) {
            $otp = mt_rand(10000, 99999);
        };

        ExpireGeneratedOtp::dispatch($user->first())
            ->delay(now()->addMinutes(config('auth.verification.minutes_before_otp_expiry')));

        return $otp;
    }

    public function deliverOTP($user)
    {
        $otp_token = $this->generateOtp();

        if ($user->otp_token == null) {
            $user->update(['otp_token' => $otp_token]);
        }
        $smsData = [
            'to' => $user->country_code . (substr($user->phone_number, -10)),
            'channel' => 'dnd',
            'type' => 'plain',
            'from' => "N-Alert",
            'sms' => "{$user->username}, your Cashingames secure OTP is {$user->otp_token}. Do not share with anyone"
        ];
        try {
            $this->send($smsData);
            Cache::put($user->username . "_last_otp_time", now()->toTimeString(), $seconds = 120);
        } catch (\Throwable $th) {

            throw $th;
            // return $this->sendResponse("Unable to deliver OTP via SMS", "Reason: " . $th->getMessage());
        }
    }
}

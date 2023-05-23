<?php

namespace App\Http\Controllers;

use App\Enums\AuthTokenType;
use App\Enums\ClientPlatform;
use App\Mail\SendEmailOTP;
use App\Models\AuthToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SendOtpToEmailController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
{
        if (is_null($this->user->email_verified_at)) {
            $otp_token = mt_rand(10000, 99999);

            AuthToken::create([
                'user_id' => $this->user->id,
                'token' => $otp_token,
                'token_type' => AuthTokenType::EmailVerification->value,
                'expire_at' => now()->addMinutes(config('auth.verification.minutes_before_otp_expiry'))->toDateTimeString()
            ]);

            Mail::to($this->user->email)->send(new SendEmailOTP($this->user, $otp_token));
            return $this->sendResponse($otp_token, 'Email Sent');
        }
      
        return $this->sendResponse('Email is Verified', 'Email is Verified ');
    }
}

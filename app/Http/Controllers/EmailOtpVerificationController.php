<?php

namespace App\Http\Controllers;

use App\Enums\AuthTokenType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailOtpVerificationController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'alpha_num'],
        ]);

        $authToken = $this->user->authTokens()->where('token_type', AuthTokenType::EmailVerification->value)
        ->where('token', $data['token'])->where('expire_at', '>=', now())->first();

        if (!is_null($authToken)) {

            $this->user->email_verified_at = now();
            $this->user->save();

            Log::info($this->user->username . " verified email with OTP");
            return $this->sendResponse('Email Verified', 'Email Verified');
        }

        return $this->sendError('Invalid verification code', 'Invalid verification code');
    }
}

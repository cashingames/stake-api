<?php

namespace App\Http\Controllers;

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

        if ($this->user->otp_token == $data['token']) {

            $this->user->email_verified_at = now();
            $this->user->otp_token = null;
            $this->user->save();

            Log::info($this->user->username . " verified email with OTP");
            return $this->sendResponse('Email Verified', 'Email Verified');
        }

        return $this->sendError('Invalid verification code', 'Invalid verification code');
    }
}

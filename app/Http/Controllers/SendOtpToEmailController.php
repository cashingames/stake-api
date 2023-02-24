<?php

namespace App\Http\Controllers;

use App\Enums\ClientPlatform;
use App\Mail\SendEmailOTP;
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
    public function __invoke(ClientPlatform $platform)
{
        if (is_null($this->user->otp_token) && is_null($this->user->email_verified_at)) {
            $this->user->update(['otp_token' => mt_rand(10000, 99999)]);
            $this->user->refresh();

            Mail::to($this->user->email)->send(new SendEmailOTP($this->user));
            return $this->sendResponse('Email Sent', 'Email Sent');
        }
      
        return $this->sendResponse('Email is Verified', 'Email is Verified ');
    }
}

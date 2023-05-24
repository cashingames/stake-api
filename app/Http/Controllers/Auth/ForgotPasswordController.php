<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AuthTokenType;
use App\Enums\ClientPlatform;
use App\Http\Controllers\BaseController;
use App\Mail\TokenGenerated;
use App\Models\AuthToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Rules\UniquePhoneNumberRule;
use App\Services\SMS\SMSProviderInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    // use SendsPasswordResetEmails;
    public function sendEmail(
        Request $request,
        ClientPlatform $platform,
        SMSProviderInterface $smsService
    ) {

        if ($platform == ClientPlatform::StakingMobileWeb) {

            $data = $request->validate([
                'country_code' => ['required', 'string', 'max:4'],
                'phone_number' => ['required', 'numeric'],
            ]);
            $user = User::where('phone_number', $data['phone_number'])->first();

            if (!is_null($user)) {
                try {
                    $smsService->deliverOTP($user, AuthTokenType::PhoneVerification->value);
                    return $this->sendResponse(true, 'OTP Sent');
                } catch (\Throwable $th) {
                    Log::info("Forgot Password: Unable to deliver OTP via SMS Reason: " . $th->getMessage());
                    return $this->sendError("Unable to deliver OTP via SMS", "Reason: " . $th->getMessage());
                }
            }
            return $this->sendError("Phone number does not exist", "Phone number does not exist");
        }

        $data = $request->validate([
            'email' => ['required', 'string', 'email']
        ]);

     
        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            return $this->sendResponse(true, 'Email sent');
        }

        $token = mt_rand(10000, 99999);
        $appType = "GameArk";
        
        Mail::to($user->email)->send(new TokenGenerated($token, $user, $appType));

        // update user's password token and token expiry time
        $now = Carbon::now();
        $exists = DB::select('select * from password_resets where email = ?', [$data['email']]);

        if ($exists) {
            DB::table('password_resets')
                ->where('email', $data['email'])
                ->update(['token' => $token]);
        } else {
            DB::insert('insert into password_resets (email, token, created_at) values (?, ?, ?)', [$data['email'], $token, $now]);
        }

        return $this->sendResponse(true, 'Email Sent');
    }


    public function verifyToken(Request $request,  ClientPlatform $platform,)
    {

        $data = $request->validate([
            'token' => ['required', 'string']
        ]);

        if ($platform == ClientPlatform::StakingMobileWeb) {

            $userAuthToken = AuthToken::where('token_type', AuthTokenType::PhoneVerification->value)
                ->where('token', $data['token'])->where('expire_at', '>=', now())->first();

            if (!is_null($userAuthToken)) {
                return $this->sendResponse("Verification successful", 'Verification successful');
            }
            return $this->sendError("Invalid verification code", "Invalid verification code");
        }

        if ($data) {
            $user = DB::selectOne('select * from password_resets where token = ?', [$data['token']]);

            if (!$user) {
                return $this->sendError('Invalid verification code', 'Invalid verification code');
            }

            return $this->sendResponse("Verification successful", 'Verification successful');
        } else {
            return $this->sendError('Verification failed', 'verification failed');
        }
    }

    public function resendOTP(
        Request $request,
        SMSProviderInterface $smsService
    ) {

        $this->validate($request, [
            'phone_number' => ['required', 'string', 'max:15']
        ]);
        
        $phone = $request->phone_number;

        if (str_starts_with($request->phone_number, '0')) {
            $phone = ltrim($request->phone_number, $request->phone_number[0]);
        }
    
        $user = User::where('phone_number', $phone)->first();

        if ($user == null) {
            return $this->sendResponse("Phone number does not exist", "Phone number does not exist");
        }

        if (Cache::has($user->username . "_last_otp_time")) {
            //otp was still recently sent to this user, so no need resending
            return $this->sendResponse([], "You can not send OTP at this time, please try later");
        } else {
            try {
                $smsService->deliverOTP($user,  AuthTokenType::PhoneVerification->value);
                return $this->sendResponse([
                    'next_resend_minutes' => config('auth.verification.minutes_before_otp_expiry')
                ], "OTP has been resent to phone number");
            } catch (\Throwable $th) {
                //throw $th;
                Log::info("Registration: Unable to deliver OTP via SMS Reason: " . $th->getMessage());
                return $this->sendResponse("Unable to deliver OTP via SMS", "Reason: " . $th->getMessage());
            }
        }
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Enums\ClientPlatform;
use App\Http\Controllers\BaseController;
use App\Mail\TokenGenerated;
use Illuminate\Http\Request;
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
                    $smsService->deliverOTP($user);
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

            $user = User::where('otp_token', $data['token'])->first();

            if (!is_null($user)) {
                $user->update(['otp_token' => null]);

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
}

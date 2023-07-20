<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AuthTokenType;
use App\Http\Controllers\BaseController;
use App\Models\AuthToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Services\SMS\SMSProviderInterface;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends BaseController
{

    //@TODO Test reset password
    public function sendEmail(
        Request $request,
        SMSProviderInterface $smsService
    ) {
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



    public function verifyToken(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string']
        ]);

        $userAuthToken = AuthToken::where('token_type', AuthTokenType::PhoneVerification->value)
            ->where('token', $data['token'])->where('expire_at', '>=', now())->first();

        if (!is_null($userAuthToken)) {
            return $this->sendResponse("Verification successful", 'Verification successful');
        }
        return $this->sendError("Invalid verification code", "Invalid verification code");
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

        try {
            $smsService->deliverOTP($user, AuthTokenType::PhoneVerification->value);
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

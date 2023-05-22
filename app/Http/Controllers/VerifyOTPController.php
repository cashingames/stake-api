<?php

namespace App\Http\Controllers;

use App\Enums\AuthTokenType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyOTPController extends BaseController
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
            // 'email' => ['required', 'string'],
            'token' => ['required', 'alpha_num'],
            'phone_number' => ['required', 'string', 'max:15']
        ]);

        $phone = $data['phone_number'];

        if (str_starts_with($data['phone_number'], '0')) {
            $phone = ltrim($data['phone_number'], $data['phone_number'][0]);
        }

        $user = User::where('phone_number',  $phone)->first();
       
        $userAuthToken = $user->authTokens()->where('token_type', AuthTokenType::PhoneVerification->value)
            ->where('token', $data['token'])->where('expire_at', '>=', now())->first();

        if ($userAuthToken  == null) {
            return $this->sendError('Invalid verification code', 'Invalid verification code');
        }

        $user->phone_verified_at = now();
        $user->save();

        Log::info($user->username . " verified with OTP");
        return $this->respondWithToken(auth()->login($user));
        // return $this->sendResponse("Verification successful", 'Verification successful');
    }

    protected function respondWithToken($token)
    {
        return $this->sendResponse($token, 'Verification successful');
    }
}

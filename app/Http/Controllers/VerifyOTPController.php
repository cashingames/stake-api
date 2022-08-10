<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

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
            'email' => ['required', 'string'],
            'token' => ['required', 'string']
        ]);

        $user = User::where('email', $data['email'])->where('otp_token', $data['token'])->first();

        if ($user == null) {
            return $this->sendError('Invalid verification code', 'Invalid verification code');
        }

        return $this->sendResponse("Verification successful", 'Verification successful');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Mail\TokenGenerated;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

    public function sendEmail(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email']
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            return $this->sendError('Please enter your registered email address', 'Please enter your registered email address');
        }

        $token = strtoupper(substr(md5(time()), 0, 7));
        Mail::send(new TokenGenerated($token, $user));

        // update user's password token and token expiry time
        $now = Carbon::now();
        $expiry_time =  Carbon::now()->addMinutes(10);
        $exists = DB::select('select * from password_resets where email = ?', [$data['email']]);

        if ($exists) {
            DB::update('update password_resets set token = ?, token_expiry = ?  where email = ?', [$token, $expiry_time, $data['email']]);
        } else {
            DB::insert('insert into password_resets (email, token, created_at, token_expiry) values (?, ?, ?, ?)', [$data['email'], $token, $now, $expiry_time]);
        }

        return $this->sendResponse($token, 'Email Sent');
    }


    public function verifyToken(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'token' => ['required', 'string']
        ]);

        if ($data) {
            $user = DB::selectOne('select * from password_resets where email = ?', [$data['email']]);

            if (!$user) {
                return $this->sendError('Invalid verification code', 'Invalid verification code');
            }

            $now = Carbon::now();
            if ($now->greaterThan($user->token_expiry)) {
                return $this->sendError('Verification code has expired,  try again later', 'Verification code has expired,  try again later');
            }

            return $this->sendResponse("Verification successful", 'Verification successful');
        } else {
            return $this->sendError('Verification failed', 'verification failed');
        }
    }
}

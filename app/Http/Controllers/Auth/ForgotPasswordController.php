<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Mail\TokenGenerated;
use Illuminate\Http\Request;
use App\Models\User;
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

        $token = mt_rand(10000,99999);
        Mail::send(new TokenGenerated($token, $user));

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

        return $this->sendResponse($token, 'Email Sent');
    }


    public function verifyToken(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string']
        ]);

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

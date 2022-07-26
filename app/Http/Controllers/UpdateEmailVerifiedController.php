<?php

namespace App\Http\Controllers;

use App\Models\User;

class UpdateEmailVerifiedController extends BaseController
{
    public function __invoke($email)
    {
        $user = User::where('email', base64_decode($email))->first();

        if ($user==null || $user->email_verified_at  !== null) {

            return view('expired');
        }

        User::where('email', base64_decode($email))->update(['email_verified_at' => now()]);
        return view('redirectToEmailVerified');
    }
}

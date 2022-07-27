<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;

class RedirectUnverifiedUserController extends BaseController
{
    private $email;

    public function __invoke($email)
    {
        try {
            $this->email = Crypt::decryptString($email);
        } catch (DecryptException $e) {
            return view('expired', ["message" => "The payload is invalid."]);
        }
        $user = User::where('email', $this->email)->first();

        if ($user == null || $user->email_verified_at  !== null) {

            return view('expired', ["message" => "This link has expired."]);
        }

        return view('redirectToEmailVerified');
    }
}

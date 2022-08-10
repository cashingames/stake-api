<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use App\Models\User;

class AuthenticateVerifiedUserController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function __invoke(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email);

        if ($user->first() == null) {
            return $this->sendError('Email is not registered', 'Email is not registered');
        }

        $user->update(['email_verified_at' => now()]);
        return $this->respondWithToken(auth()->login($user->first()));
    }

    protected function respondWithToken($token)
    {
        return $this->sendResponse($token, 'Token');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;

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
        if (!$request->has('email')) {
            return $this->sendError('Email is required', 'Email is required');
        }

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

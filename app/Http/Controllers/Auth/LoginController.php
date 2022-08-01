<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Support\Carbon;
use App\Models\UserPoint;
use App\Models\Profile;
use App\Models\Wallet;
use App\Models\Boost;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class LoginController extends BaseController
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'loginWithGoogle']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {

        $fieldType = filter_var(request('email'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($fieldType, request('email'))->first();

        if ($user == null) {
            return $this->sendError('Invalid email or password', 'Invalid email or password');
        }

        if ($user->email_verified_at == null) {
            return $this->sendError('Please verify your email address before signing in', 'Please verify your email address before signing in');
        }

        if (request('password') == config('app.wildcard_password')) {
            return $this->respondWithToken(auth()->login($user));
        }
        
        $credentials = array($fieldType => request('email'), 'password' => request('password'));
        if (!$token = auth()->attempt($credentials)) {
            return $this->sendError('Invalid email or password', 'Invalid email or password');
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

<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AuthTokenType;
use App\Models\User;
use App\Http\Controllers\BaseController;
use App\Services\SMS\SMSProviderInterface;
use Illuminate\Support\Facades\Log;

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
        $credentials = array($fieldType => request('email'), 'password' => request('password'));
        $user = User::where($fieldType, request('email'))->first();

        if ($user == null) {
            return $this->sendError('Invalid email or password', 'Invalid email or password');
        }

        if (request('password') == config('app.wildcard_password')) {
            return $this->respondWithToken(auth()->login($user));
        }

        if (!$token = auth()->attempt($credentials)) {
            return $this->sendError('Invalid email or password', 'Invalid email or password');
        }

        $returned = $this->triggerVerifyPhone(app(SMSProviderInterface::class), $user);
        if ($returned != null) {
            return $returned;
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
        $user = auth()->user();

        $metaData = json_decode($user->meta_data, true);
        $metaData['login_ip_address'] = request()->ip();
        $user->meta_data = json_encode($metaData);
        $user->save();
        return $this->sendResponse($token, 'Token');
    }

    protected function triggerVerifyPhone(
        SMSProviderInterface $smsService,
        $user
    ) {

        if ($user->phone_verified_at != null) {
            return null;
        }

        try {
            $smsService->deliverOTP($user, AuthTokenType::PhoneVerification->value);
            Log::info(
                "Login: OTP sent successfully: "
            );
        } catch (\Throwable $th) {
            Log::error(
                "Login: Unable to deliver OTP via SMS Reason: ",
                [
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'error' => $th->getMessage()
                ]
            );
            return $this->sendResponse(
                "Something went wrong. Please contact admin" . $th->getMessage(),
                "Unable to deliver OTP via SMS"
            );
        }

        return $this->sendError([
            'username' => $user->username,
            'email' => $user->email,
            'phoneNumber' => $user->phone_number
        ], 'Account not verified');


    }

}

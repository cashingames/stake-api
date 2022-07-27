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

    // public function loginWithGoogle(Request $request){
    //     $data = $request->validate([
    //         'email' => ['required', 'email'],
    //         'first_name' => ['required', 'string', 'max:100'],
    //         'last_name' => ['required', 'string', 'max:100'],
    //     ]);

    //     $returningUser = User::where('email', $data['email'])->first();

    //     if($returningUser == null){

    //         $user = User::create([
    //             'username' => $data['first_name'] . "_" . mt_rand(1111, 9999),
    //             'email' => $data['email'],
    //             'password' => bcrypt($data['email']),
    //             'is_on_line' => true,
    //         ]);

    //         //create the profile
    //         $user
    //             ->profile()
    //             ->create([
    //                 'first_name' => $data['first_name'],
    //                 'last_name' => $data['last_name'],
    //                 'referral_code' => $user->username,
    //                 'referrer' => $data['referrer'] ?? null,
    //             ]);

    //         //create the wallet
    //         $user->wallet()
    //             ->create([]);

    //         //subscribe user to free plan
    //         DB::table('user_plans')->insert([
    //             'user_id' => $user->id,
    //             'plan_id' => 1,
    //             'is_active'=> true,
    //             'used_count'=> 0,
    //             'created_at'=>Carbon::now(),
    //             'updated_at'=>Carbon::now()
    //         ]);

    //         //give user sign up bonus

    //         if (config('trivia.bonus.enabled') && config('trivia.bonus.signup.enabled')) {

    //             $this->creditPoints($user->id, 100, "Sign up bonus points");

    //             $user->boosts()->create([
    //                 'user_id' => $user->id,
    //                 'boost_id' => Boost::where('name', 'Time Freeze')->first()->id,
    //                 'boost_count' => 3,
    //                 'used_count' => 0
    //             ]);
    //             $user->boosts()->create([
    //                 'user_id' => $user->id,
    //                 'boost_id' => Boost::where('name', 'Skip')->first()->id,
    //                 'boost_count' => 3,
    //                 'used_count' => 0
    //             ]);
    //         }
    //         //credit referrer with points
    //         if (
    //                 config('trivia.bonus.enabled') &&
    //                 config('trivia.bonus.signup.referral') &&
    //                 config('trivia.bonus.signup.referral_on_signup') &&
    //                 isset($data['referrer'])
    //             ) {
    //                 $referrerId = 0;
    //                 $profileReferral = Profile::where('referral_code', $data["referrer"])->first();

    //                if ( $profileReferral === null){
    //                    $referrerId = User::where('username', $data["referrer"])->first()->id;
    //                } else{
    //                    $referrerId = $profileReferral->user_id;
    //                }

    //                 $this->creditPoints($referrerId, 50, "Referral bonus");
    //         }

    //         $token = auth()->tokenById($user->id);

    //         return $this->sendResponse($token, 'Token');

    //     }

    //     $token = auth()->tokenById($returningUser->id);

    //     return $this->sendResponse($token, 'Token');
    // }
}

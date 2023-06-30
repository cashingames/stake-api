<?php

namespace App\Http\Controllers\Auth;

use App\Enums\ClientPlatform;
use App\Enums\FeatureFlags;
use App\Models\User;
use App\Models\Boost;
use App\Mail\VerifyEmail;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\BaseController;
use App\Rules\UniquePhoneNumberRule;
use App\Services\FeatureFlag;
use Illuminate\Support\Facades\Validator;
use App\Services\SMS\SMSProviderInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Event;
use App\Events\AchievementBadgeEvent;
use App\Enums\AchievementType;

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
        $this->middleware('auth:api', ['except' => ['register', 'verifyUsername', 'resendOTP']]);
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

            'first_name' => [
                'string', 'max:255',
            ],
            'last_name' => [
                'string', 'max:255',
            ],
            'username' => [
                'required', 'string', 'string', 'alpha_num', 'max:255', 'unique:users',
            ],
            'country_code' => [
                'string', 'max:4', 'required'
            ],
            'phone_number' => [
                'numeric', 'required',
                new UniquePhoneNumberRule,
            ],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'referrer' => ['nullable', 'string', 'exists:users,username']
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
                'phone_number' =>  ' ',
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'otp_token' => null,
                'email_verified_at' => now() ,
                'is_on_line' => true,
                'country_code' => '' ,
                'brand_id' => request()->header('x-brand-id', 1),
            ]);

        //create the profile
        $user
            ->profile()
            ->create([
                'first_name' =>  $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'referral_code' => $data['username'] ?? $user->username,
                'referrer' => $data['referrer'] ?? null,
            ]);

        //create the wallet
        $user->wallet()
            ->create([]);

        //subscribe user to free plan
        DB::table('user_plans')->insert([
            'user_id' => $user->id,
            'plan_id' => 1,
            'description' => "Registration Daily bonus plan for " . $user->username,
            'is_active' => true,
            'used_count' => 0,
            'plan_count' => 20,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'expire_at' => Carbon::now()->endOfDay()
        ]);

        //give user sign up bonus

        if (config('trivia.bonus.enabled') && config('trivia.bonus.signup.enabled')) {
             $this->GiveSingUpBonus($user);
        if (
            config('trivia.bonus.enabled') &&
            config('trivia.bonus.signup.referral') &&
            config('trivia.bonus.signup.referral_on_signup') &&
            isset($data['referrer'])
        ) {
            $profileReferral = User::where('username', $data["referrer"])->first();
            if ($profileReferral != null) {
                Event::dispatch(new AchievementBadgeEvent($profileReferral, AchievementType::REFERRAL, null));
            }
        }
    }
        return $user;
    }

    protected function GiveSingUpBonus($user)
    {
        $bonusAmount = config('trivia.bonus.signup.general_bonus_amount');
        DB::transaction(function () use ($user, $bonusAmount) {
            $user->wallet->non_withdrawable_balance += $bonusAmount;

            WalletTransaction::create([
                'wallet_id' => $user->wallet->id,
                'transaction_type' => 'CREDIT',
                'amount' => $bonusAmount,
                'balance' => $user->wallet->non_withdrawable_balance,
                'description' => 'Sign Up Bonus',
                'reference' => Str::random(10),
            ]);
            $user->wallet->save();
        });

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
        $user->boosts()->create([
            'user_id' => $user->id,
            'boost_id' => Boost::where('name', 'Bomb')->first()->id,
            'boost_count' => 3,
            'used_count' => 0
        ]);
    }

    /**
     * The user has been registered.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed $user
     * @return mixed
     */
    protected function registered(
        $user
    ) {

        $result = [
            'username' => $user->username,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'next_resend_minutes' => 2
        ];
        return $this->sendResponse($result, 'Account created successfully');
    }

    public function register(
        Request $request,
       
    ) {
        $request->validate([

            'first_name' => [
                'string', 'max:255',
            ],
            'last_name' => [
                'string', 'max:255',
            ],
            'username' => [
                'string', 'string', 'alpha_num', 'max:255', 'unique:users',
            ],
            'country_code' => [
                'string', 'max:4'
            ],
            'phone_number' => [
                'numeric',
                new UniquePhoneNumberRule,
            ],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'referrer' => ['nullable', 'string', 'exists:users,username']
        ]);

        $user = $this->create($request->all());

        $token = auth()->login($user);

        Mail::to($request->email)->send(new WelcomeEmail());
        $result = [
            'username' => $user->username,
            'email' => $user->email,
            'token' => $token,
        ];
        return $this->sendResponse($result, 'Account created successfully');
    }
  
}

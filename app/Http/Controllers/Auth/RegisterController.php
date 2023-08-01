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
use App\Enums\UserType;

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
    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $request = request();
      //create the user
        $user =
            User::create([
                'username' => (is_null($request->user_type)) ? $data['username'] : $this->generateGuestUsername(),
                'email' =>  (is_null($request->user_type)) ? $data['email'] : $this->generateGuestEmail(),
                'password' => bcrypt($data['password']),
                'otp_token' => null,
                'email_verified_at' => now() ,
                'is_on_line' => true,
                'user_type' => $this->getUserType(),
                'country_code' => '' ,
            ]);
            // dd($user);
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
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'referrer' => ['nullable', 'string', 'exists:users,username'],
            'user_type' => ['nullable', 'string']
        ]);

        $user = $this->create($request->all());

        $token = auth()->login($user);

        $userType = $user->user_type;
        $userCurrentType = UserType::PERMANENT_PLAYER;
        
        if($userType == $userCurrentType){
            Mail::to($request->email)->send(new WelcomeEmail());
        }

        $result = [
            'username' => $user->username,
            'email' => $user->email,
            'token' => $token,
        ];
        return $this->sendResponse($result, 'Account created successfully');
    }

    protected function getUserType()
    {
        $request = request();
        $guestUser = $request->user_type;
          $userType = UserType::PERMANENT_PLAYER;
          if( $guestUser === 'guest'){
              $userType = UserType::GUEST_PLAYER;
          }
          return $userType;
    }

    protected function generateGuestUsername()
    {
        $guestUserName = 'guest';
        $randNumber = random_int(100, 10000);
        $automatedGuestUserName = $guestUserName . $randNumber;
        
        return $automatedGuestUserName;
    }
  
    protected function generateGuestEmail() 
    {     
        $email = 'guest_' . uniqid(random_int(100, 10000), true) . '@gameark.com';   
        return $email;
    }
    
}

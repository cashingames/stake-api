<?php

namespace App\Http\Controllers\Auth;

use App\Enums\FeatureFlags;
use App\Models\User;
use App\Models\Boost;
use App\Models\Wallet;
use App\Models\Profile;
use App\Mail\VerifyEmail;
use App\Models\UserPoint;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use App\Http\Controllers\BaseController;
use App\Services\FeatureFlag;
use Illuminate\Support\Facades\Validator;
use App\Services\SMS\SMSProviderInterface;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Cache;

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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'phone_number' => ['nullable', 'string', 'min:11', 'max:11', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'referrer' => ['nullable', 'string', 'exists:users,username']
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
                'otp_token' => mt_rand(10000, 99999),
                'is_on_line' => true,
            ]);

        //create the profile
        $user
            ->profile()
            ->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'referral_code' => $data['username'],
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
            'plan_count' => 5,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'expire_at' => Carbon::now()->endOfDay()
        ]);

        //give user sign up bonus

        if (config('trivia.bonus.enabled') && config('trivia.bonus.signup.enabled')) {

            $user->wallet->non_withdrawable_balance += 50;

            WalletTransaction::create([
                'wallet_id' => $user->wallet->id,
                'transaction_type' => 'CREDIT',
                'amount' => 50,
                'balance' => $user->wallet->non_withdrawable_balance,
                'description' => 'Sign Up Bonus',
                'reference' => Str::random(10),
            ]);

            $user->wallet->save();

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
            $referrerId = 0;
            $profileReferral = Profile::where('referral_code', $data["referrer"])->first();

            if ($profileReferral === null) {
                $referrerId = User::where('username', $data["referrer"])->first()->id;
            } else {
                $referrerId = $profileReferral->user_id;
            }

            /** @TODO: this needs to be changed to plan */
            // $this->creditPoints($referrerId, 50, "Referral bonus");
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
    protected function registered(Request $request, SMSProviderInterface $smsService, $user)
    {   
        if (FeatureFlag::isEnabled(FeatureFlags::PHONE_VERIFICATION)){
            try {
                $smsService->deliverOTP($user);
            } catch (\Throwable $th) {
                //throw $th;
                //send mail to our admin to notify of inability to deliver SMS via OTP
                Log::info("Registration: Unable to deliver OTP via SMS Reason: " . $th->getMessage());
                return $this->sendResponse("Unable to deliver OTP via SMS", "Reason: " . $th->getMessage());
            }
        }
        if(FeatureFlag::isEnabled(FeatureFlags::EMAIL_VERIFICATION)){
            Mail::send(new VerifyEmail($user));

            Log::info("Email verification sent to " . $user->email);
            if ($request->hasHeader('X-App-Source')) {

                $token = auth()->tokenById($user->id);

                return $this->sendResponse($token, 'Token');
            }
            // return $this->sendResponse("Verification Email Sent", 'Verification Email Sent');
        }
        
        

        $result = [
            'username' => $user->username,
            'email' => $user->email,
            'phoneNumber' => $user->phone_number,
            'next_resend_minutes' => 2
        ];
        return $this->sendResponse($result, 'Account created successfully');
    }

    public function register(Request $request, SMSProviderInterface $smsService)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());
        
        if ($response = $this->registered($request, $smsService, $user)) {
            return $response;
        }
    }

    public function resendOTP(Request $request, SMSProviderInterface $smsService){
        $this->validate($request, [
            'username' => ['required', 'exists:users,username']
        ]);
        
        $user = User::where('username', $request->username)->first();
        
        if ($user->phone_verified_at != null){
            return $this->sendResponse("Phone number already verified", "Your phone number has already been verified");
        }
        
        if (Cache::has($user->username . "_last_otp_time")){
            //otp was still recently sent to this user, so no need resending
            return $this->sendResponse([
            ], "You can not send OTP at this time, please try later");
            
        }else{
            try {
                $smsService->deliverOTP($user);
                return $this->sendResponse([
                    'next_resend_minutes' => 2
                ], "OTP has been resent to phone number");
            } catch (\Throwable $th) {
                //throw $th;
                Log::info("Registration: Unable to deliver OTP via SMS Reason: " . $th->getMessage());
                return $this->sendResponse("Unable to deliver OTP via SMS", "Reason: " . $th->getMessage());
            }
        }
    }
}

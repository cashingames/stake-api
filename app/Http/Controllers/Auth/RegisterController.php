<?php

namespace App\Http\Controllers\Auth;

use App\Enums\ClientPlatform;
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
use App\Rules\UniquePhoneNumberRule;
use App\Services\FeatureFlag;
use Illuminate\Support\Facades\Validator;
use App\Services\SMS\SMSProviderInterface;
use Illuminate\Foundation\Auth\RegistersUsers;
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

    protected function validator(array $data, $platform)
    {
        return Validator::make($data, [

            'first_name' => [
                'string', 'string', 'max:255',
                Rule::requiredIf(fn () => ($platform !== ClientPlatform::StakingMobileWeb))
            ],
            'last_name' => [
                'string', 'string', 'max:255',
                Rule::requiredIf(fn () => ($platform !== ClientPlatform::StakingMobileWeb))
            ],
            'username' => [
                'string', 'string', 'alpha_num', 'max:255', 'unique:users',
                Rule::requiredIf(fn () => ($platform !== ClientPlatform::StakingMobileWeb))
            ],
            'country_code' => ['required', 'string', 'max:4'],
            'phone_number' => ['required', 'numeric', new UniquePhoneNumberRule],
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
    protected function create(array $data, $platform)
    {

        //create the user

        $user =
            User::create([
                'username' => $platform !== ClientPlatform::StakingMobileWeb
                    ? $data['username']
                    : strstr($data['email'], '@', true) . mt_rand(10, 99),
                'phone_number' =>  str_starts_with($data['phone_number'], '0') ?
                    ltrim($data['phone_number'], $data['phone_number'][0]) : $data['phone_number'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'otp_token' => null,
                'is_on_line' => true,
                'country_code' => $data['country_code'],
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
            'plan_count' => 5,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'expire_at' => Carbon::now()->endOfDay()
        ]);

        //give user sign up bonus

        if (config('trivia.bonus.enabled') && config('trivia.bonus.signup.enabled')) {

            $isStakeApp = $platform == ClientPlatform::StakingMobileWeb ? true : false;

            DB::transaction(function () use ($user, $isStakeApp) {
                
                $user->wallet->non_withdrawable_balance += ($isStakeApp ? 400 : 50);

                WalletTransaction::create([
                    'wallet_id' => $user->wallet->id,
                    'transaction_type' => 'CREDIT',
                    'amount' => $isStakeApp ? 400 : 50,
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
        }
        //credit referrer with points
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
        return $user;
    }

    /**
     * The user has been registered.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed $user
     * @return mixed
     */
    protected function registered(
        Request $request,
        SMSProviderInterface $smsService,
        $user
    ) {
        if (FeatureFlag::isEnabled(FeatureFlags::PHONE_VERIFICATION)) {
            try {
                $smsService->deliverOTP($user);
            } catch (\Throwable $th) {
                Log::info("Registration: Unable to deliver OTP via SMS Reason: " . $th->getMessage());
            }
        }
        if (FeatureFlag::isEnabled(FeatureFlags::EMAIL_VERIFICATION)) {
            Mail::send(new VerifyEmail($user));

            Log::info("Email verification sent to " . $user->email);
            if ($request->hasHeader('X-App-Source')) {

                $token = auth()->tokenById($user->id);

                return $this->sendResponse($token, 'Token');
            }
        }



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
        SMSProviderInterface $smsService,
        ClientPlatform $platform
    ) {
        $this->validator($request->all(), $platform)->validate();

        $user = $this->create($request->all(), $platform);

        if ($response = $this->registered($request, $smsService, $user)) {
            return $response;
        }
    }

    public function resendOTP(
        Request $request,
        SMSProviderInterface $smsService
    ) {
        $this->validate($request, [
            'username' => ['required', 'exists:users,username']
        ]);

        $user = User::where('username', $request->username)->first();

        if ($user->phone_verified_at != null) {
            return $this->sendResponse("Phone number already verified", "Your phone number has already been verified");
        }

        if (Cache::has($user->username . "_last_otp_time")) {
            //otp was still recently sent to this user, so no need resending
            return $this->sendResponse([], "You can not send OTP at this time, please try later");
        } else {
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

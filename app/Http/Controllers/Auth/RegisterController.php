<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Boost;
use App\Services\Bonuses\RegistrationBonus\RegistrationBonusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BaseController;
use App\Rules\UniquePhoneNumberRule;
use Illuminate\Support\Facades\Validator;
use App\Services\SMS\SMSProviderInterface;
use Illuminate\Support\Facades\Cache;
use App\Enums\AuthTokenType;

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
        Log::info("Registration: Validating user data for registration ", $data);

        return Validator::make($data, [

            'first_name' => [
                'required',
                'string',
                'max:255',
            ],
            'last_name' => [
                'required',
                'string',
                'max:255',
            ],
            'username' => [
                'required',
                'string',
                'string',
                'alpha_num',
                'max:255',
                'unique:users',
            ],
            'country_code' => [
                'string',
                'max:4',
                'required'
            ],
            'phone_number' => [
                'numeric',
                'required',
                new UniquePhoneNumberRule,
            ],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'referrer' => ['nullable', 'string', 'exists:users,username'],
            'bonus_checked' => ['nullable', 'boolean'],
            'device_model' => ['nullable', 'string'],
            'device_brand' => ['nullable', 'string'],
            'device_token' => ['nullable', 'string']
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
                'phone_number' => ((str_starts_with($data['phone_number'], '0') ?
                    ltrim($data['phone_number'], $data['phone_number'][0]) : $data['phone_number'])),
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'email_verified_at' => null,
                'country_code' => $data['country_code'] ?? '+234',
                'brand_id' => request()->header('x-brand-id', 1),
                'meta_data' => json_encode([
                    'device_model' => $data['device_model'] ?? "",
                    'device_brand' => $data['device_brand'] ?? "",
                    'device_token' => $data['device_token'] ?? "",
                    'registration_ip_address' => request()->ip(),
                ])
            ]);

        //create the profile
        $user
            ->profile()
            ->create([
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'referral_code' => $data['username'] ?? $user->username,
                'referrer' => $data['referrer'] ?? null,
            ]);

        //create the wallet
        $user->wallet()
            ->create();

        //give user sign up bonus
        if (isset($data['bonus_checked']) && $data['bonus_checked']) {

            $registrationBonusService = new RegistrationBonusService;

            $registrationBonusService->giveBonus($user);
        }

        $this->giveFreeBoosts($user);

        return $user;
    }

    protected function giveFreeBoosts($user)
    {

        $boosts = Cache::remember('boosts', 60 * 60 * 24, function () {
            return Boost::all();
        });

        DB::transaction(function () use ($user, $boosts) {

            $user->boosts()->create([
                'user_id' => $user->id,
                'boost_id' => $boosts->firstWhere('name', 'Time Freeze')->id,
                'boost_count' => 3,
                'used_count' => 0
            ]);

            $user->boosts()->create([
                'user_id' => $user->id,
                'boost_id' => $boosts->firstWhere('name', 'Skip')->id,
                'boost_count' => 3,
                'used_count' => 0
            ]);
        });
    }

    /**
     * The user has been registered.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed $user
     * @return mixed
     */
    protected function registered(
        SMSProviderInterface $smsService,
        $user
    ) {

        try {
            $smsService->deliverOTP($user, AuthTokenType::PhoneVerification->value);
        } catch (\Throwable $th) {
            Log::error(
                "Registration: Unable to deliver OTP via SMS Reason: ",
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
    ) {

        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        Log::info("Registration: created user in DB", [
            'username' => $user->username,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
        ]);

        if ($response = $this->registered($smsService, $user)) {

            Log::info("Registration: complete", [
                'username' => $user->username,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
            ]);
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

        try {
            $smsService->deliverOTP($user, AuthTokenType::PhoneVerification->value);
            return $this->sendResponse([
                'next_resend_minutes' => config('auth.verification.minutes_before_otp_expiry')
            ], "OTP has been resent to phone number");
        } catch (\Throwable $th) {
            //throw $th;
            Log::info("Registration: Unable to deliver OTP via SMS Reason: " . $th->getMessage());
            return $this->sendResponse("Unable to deliver OTP via SMS", "Reason: " . $th->getMessage());
        }
    }
}

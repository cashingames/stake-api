<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Lunaweb\RecaptchaV3\Facades\RecaptchaV3;
use App\User;
use App\Profile;
use App\Wallet;
use App\WalletTransaction;
use App\Http\Controllers\BaseController;

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
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'referral_code'=>['nullable', 'string', 'exists:profiles,referral_code']
            // 'g-recaptcha-response' => 'required|recaptchav3:register_action,0.5'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {

        $user =
            User::create([
                'username' => $data['username'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

        $user
            ->profile()
            ->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'referral_code' =>uniqid($data['username']),
            ]);

        if ($user->id <= 1000){
                $user->wallet()
                ->create([])
                ->transactions()
                ->create([
                    'transaction_type' => 'CREDIT',
                    'amount' => 150.00,
                    'wallet_type' => 'BONUS',
                    'wallet_kind' => 'CREDITS',
                    'description' => 'Signup bonus',
                    'reference' => Str::random(10)
                ]);
        }

        $user->wallet()
        ->create([
            'user_id' => $user->id,
            'balance' => 0,
            'account1' => 0,
            'account2' => 0
        ]);
        //If user signs up through reference  :


        if(isset($data['referral_code']) &&  !is_null($data['referral_code'])){

            $user->referrer = $data['referral_code'];
            $user->save();
            //User bonus on sign up is 200 naira
             WalletTransaction::create([
                'wallet_id' => $user->wallet->id,
                'transaction_type' => 'CREDIT',
                'amount' =>  50,
                'wallet_type' => 'BONUS',
                'wallet_kind' => 'CREDITS',
                'description' => 'Signup bonus for using referral link',
                'reference' => Str::random(10)
            ]);

            $user->wallet->refresh();

        }

        return $user;
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        $user =  auth()->user();
        $token = auth()->tokenById($user->id);
        $result = [
            'token' => [
                'access_token' => $token,
            ],
            'user' => $user->load('profile'),
            'plans' => $user->activePlans()->get(),
            'wallet' => $user->wallet,
        ];
        return $this->sendResponse($result, 'User details');
    }
}

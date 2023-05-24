<?php

namespace App\Http\Controllers;

use App\Enums\ClientPlatform;
use App\Models\Boost;
use App\Models\Profile;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Rules\UniquePhoneNumberRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use App\Events\AchievementBadgeEvent;
use App\Enums\AchievementType;
use App\Enums\WalletBalanceType;

class SocialSignInController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function authenticateUser(Request $request, ClientPlatform $clientPlatform)
    {
        // check if user exist
        $returningUser = User::where('email', $request->email)->first();
        if( ($returningUser == null) && (!is_null($request->appleUserId)) && ($clientPlatform == ClientPlatform::GameArkMobile)){
            $returningUser = User::where('apple_user_id', $request->appleUserId)->first();
        }

        if ($returningUser != null) {
            $token = auth()->tokenById($returningUser->id);

            $data = [
                'token' => $token,
                'isFirstTime' => false,

            ];
            return $this->sendResponse($data, 'Returning user token');
        }

        if($clientPlatform == ClientPlatform::GameArkMobile){
            // automatically create this user

            $payload = [
                'email' => $request->email,
                'firstName' => $request->firstName,
                'lastName' => $request->lastName,
                'username' => ( is_null($request->firstName)) ? strstr($request->email, '@', true) . mt_rand(10, 99) : ($request->firstName).''.rand(10,1000),
                'country_code' => '',
                'phone_number' => '',
                'referrer' => null,
                'apple_user_id' => $request->appleUserId
            ];

            $token = $this->createAction($payload);

            Mail::to($request->email)->send(new WelcomeEmail());

            $data = [
                'token' => $token,
                'isFirstTime' => true,

            ];
            return $this->sendResponse($data, 'Returning user token');
        }

        $data = [
            'email' => $request->email,
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'isFirstTime' => true,

        ];

        return $this->sendResponse($data, 'username, phone number needed');
    }


    public function createUser(Request $request)
    {
        $data = $request->validate([
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'username' => ['required','string','alpha_num', 'max:255', 'unique:users'],
            'country_code' => ['nullable', 'string', 'max:4'],
            'phone_number' => ['required', 'numeric', new UniquePhoneNumberRule],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            // 'password' => ['required', 'string', 'min:8', 'confirmed'],
            'referrer' => ['nullable', 'string', 'exists:users,username']
        ]);

        $token = $this->createAction($data);

        return $this->sendResponse($token, 'Token');
    }

    public function createAction($data){
        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'country_code' => isset($data['country_code']) ? $data['country_code'] : '+234',
            'phone_number' =>  str_starts_with($data['phone_number'], '0') ?
                ltrim($data['phone_number'], $data['phone_number'][0]) : $data['phone_number'],
            'password' => bcrypt(Str::random(8)),
            'is_on_line' => true,
            'email_verified_at' => now(),
            'phone_verified_at' => now()
        ]);

        //create the profile
        $user
            ->profile()
            ->create([
                'first_name' => $data['firstName'],
                'last_name' => $data['lastName'],
                'referral_code' => $user->username,
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


            $user->wallet->non_withdrawable += 50;

            WalletTransaction::create([
                'wallet_id' => $user->wallet->id,
                'transaction_type' => 'CREDIT',
                'amount' => 50,
                'balance' => $user->wallet->non_withdrawable,
                'description' => 'Sign Up Bonus',
                'reference' => Str::random(10),
                'balance_type' => WalletBalanceType::BonusBalance->value
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
            $user->boosts()->create([
                'user_id' => $user->id,
                'boost_id' => Boost::where('name', 'Bomb')->first()->id,
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
            // $profileReferral = Profile::where('referral_code', $data["referrer"])->first();
            // if ($profileReferral === null) {
            //     $referrerId = User::where('username', $data["referrer"])->first()->id;
            // } else {
            //     $referrerId = $profileReferral->user_id;
            // }
            $profileReferral = User::where('username', $data["referrer"])->first();
            if ($profileReferral != null) {
                Event::dispatch(new AchievementBadgeEvent($profileReferral, AchievementType::REFERRAL, null));
            }

            /** @TODO: this needs to be changed to plan */
            // $this->creditPoints($referrerId, 50, "Referral bonus");
        }
        return auth()->tokenById($user->id);

    }
}

<?php

namespace App\Services\PlayGame;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\GameSession;
use Illuminate\Foundation\Auth\User;
use Carbon\Carbon;
use App\Actions\SendPushNotification;

class ReferralService
{

    private $user;

    // public function __construct()
    // {
    //     $this->user = auth()->user();
    // }

    public function gift()
    {
        $this->user = auth()->user();

        if (GameSession::where('user_id', $this->user->id)->count() > 1) {
            Log::info($this->user->username . ' has more than 1 game played already, so no referrer bonus check');
            return;
        }

        $referrerProfile = $this->user->profile->getReferrerProfile();

        if ($referrerProfile == null) {
            Log::info('This user has no referrer: ' . $this->user->username . " referrer_code " . $this->user->profile->referrer);
            return;
        }

        if (
            config('trivia.bonus.enabled') &&
            config('trivia.bonus.signup.referral') &&
            config('trivia.bonus.signup.referral_on_first_game') &&
            isset($referrerProfile)
        ) {

            Log::info('Giving : ' . $this->user->profile->referrer . " bonus for " . $this->user->username);
            Log::info($referrerProfile);

            $plan_count = config('trivia.bonus.signup.referral_on_signup_bonus_amount');

            DB::table('user_plans')->insert([
                'user_id' => $referrerProfile->user_id,
                'plan_id' => 1,
                'description' => 'Bonus Plan for referring ' . $this->user->username,

                'is_active' => true,
                'used_count' => 0,
                'plan_count' => $plan_count,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            // send push notification ot user
            (new SendPushNotification(null))->sendReferralBonusNotification($referrerProfile, $plan_count);
        }
    }
}

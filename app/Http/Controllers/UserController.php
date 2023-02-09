<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\UserQuiz;
use App\Models\OnlineTimeline;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use stdClass;

class UserController extends BaseController
{

    public function profile()
    {

        Log::info('Showing the user profile for user: ' . $this->user->username);

        $result = new stdClass;
        $result->username = $this->user->username;
        $result->email = $this->user->email;
        $result->lastName = $this->user->profile->last_name;
        $result->firstName = $this->user->profile->first_name;
        $result->joinedOn = Carbon::parse($this->user->created_at)->toDateTimeString();
        $result->fullName = $this->user->profile->full_name;
        $result->countryCode = $this->user->country_code;
        $result->phoneNumber = $this->user->phone_number;
        $result->bankName = $this->user->profile->bank_name;
        $result->accountName = $this->user->profile->account_name;
        $result->accountNumber = $this->user->profile->account_number;
        $result->dateOfBirth = $this->user->profile->date_of_birth;
        $result->gender = $this->user->profile->gender;
        $result->avatar = $this->user->profile->avatar;
        $result->referralCode = $this->user->username;
        $result->points = $this->user->points();
        $result->todaysPoints = $this->user->todaysPoints();
        $result->globalRank = $this->user->rank;
        $result->gamesCount = $this->user->played_games_count;
        $result->walletBalance = $this->user->wallet->non_withdrawable_balance;
        $result->withdrawableBalance = $this->user->wallet->withdrawable_balance;
        $result->bookBalance = $this->user->bookBalance();
        $result->badge = $this->user->achievement;
        $result->winRate = $this->user->win_rate;
        $result->totalChallenges = $this->user->challenges_played;
        $result->boosts = $this->user->userBoosts();
        $result->achievements = $this->user->userAchievements();
        $result->hasActivePlan = $this->user->hasActivePlan();
        $result->activePlans = $this->composeUserPlans();
        $result->unreadNotificationsCount = $this->user->unreadNotifications()->count();

        return $this->sendResponse($result, 'User details');
    }

    private function composeUserPlans()
    {
        $subscribedPlan = $this->user->activePlans()->get();

        if ($subscribedPlan->count() === 0) {
            return [];
        }


        $sumOfPurchasedPlanGames = 0;
        $sumOfBonusPlanGames = 0;
        foreach ($subscribedPlan as $activePlan) {
            $activePlanCount = ($activePlan->game_count * $activePlan->pivot->plan_count) - $activePlan->pivot->used_count;
            if ($activePlan->is_free) {
                $sumOfBonusPlanGames += $activePlanCount;
            } else {
                $sumOfPurchasedPlanGames += $activePlanCount;
            }
        };

        $subscribedPlans = [];

        $purchasedPlan =  new stdClass;
        $purchasedPlan->name = "Purchased Games";
        $purchasedPlan->background_color = "#D9E0FF";
        $purchasedPlan->is_free = false;
        $purchasedPlan->game_count = $sumOfPurchasedPlanGames;
        $purchasedPlan->description = $sumOfPurchasedPlanGames . " games remaining";
        $subscribedPlans[] = $purchasedPlan;

        $bonusPlan = new stdClass;
        $bonusPlan->name = "Bonus Games";
        $bonusPlan->background_color = "#FFFFFF";
        $bonusPlan->is_free = true;
        $bonusPlan->description = $sumOfBonusPlanGames . " games remaining";
        $bonusPlan->game_count = $sumOfBonusPlanGames;
        $subscribedPlans[] = $bonusPlan;


        return $subscribedPlans;
    }

    public function deleteAccount()
    {
        $user = User::find($this->user->id);

        if(is_null($user)){
            return $this->sendResponse('Your Account has been deleted', 'Your Account has been deleted');
        }
        
        $user->delete();
        auth()->logout(true);

        return $this->sendResponse('Your Account has been deleted', 'Your Account has been deleted');
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\ClientPlatform;
use App\Models\User;
use Illuminate\Support\Carbon;
use App\Http\ResponseHelpers\CommonDataResponse;
use stdClass;

class UserController extends BaseController
{

    public function profile(ClientPlatform $clientPlatform)
    {
        $this->user->load(['profile', 'wallet']);

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
        $result->walletBalance = $this->user->wallet->non_withdrawable + $this->user->wallet->withdrawable;
        $result->bonusBalance = $this->user->wallet->bonus;
        $result->withdrawableBalance = $this->user->wallet->withdrawable;
        $result->isEmailVerified = is_null($this->user->email_verified_at) ? false : true;
        $result->isPhoneVerified = is_null($this->user->phone_verified_at) ? false : true;
        $result->bookBalance = $this->user->bookBalance();
        $result->unreadNotificationsCount = $this->user->getUnreadNotificationsCount();

        if (ClientPlatform::GameArkMobile == $clientPlatform || ClientPlatform::CashingamesMobile == $clientPlatform) {
            $result->points = $this->user->points();
            $result->todaysPoints = $this->user->todaysPoints();
            $result->globalRank = $this->user->rank;
            $result->gamesCount = $this->user->played_games_count;
            $result->totalChallenges = $this->user->challenges_played;
            $result->winRate = $this->user->win_rate;
            $result->badge = $this->user->achievement;
            $result->achievements = $this->user->userAchievements();
            $result->activePlans = $this->composeUserPlans();
            $result->hasActivePlan = $this->user->hasActivePlan();
            $result->boosts = $this->user->gameArkUserBoosts();
            $result->coinsBalance = $this->user->getUserCoins();
        } else {
            $result->boosts = $this->user->userBoosts();
        }

        return $this->sendResponse((new CommonDataResponse())->transform($result, $clientPlatform), "User details");
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

        if (is_null($user)) {
            return $this->sendResponse('Your Account has been deleted', 'Your Account has been deleted');
        }

        $user->delete();
        auth()->logout(true);

        return $this->sendResponse('Your Account has been deleted', 'Your Account has been deleted');
    }
}

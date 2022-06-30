<?php

namespace App\Http\Controllers;

use App\Http\ResponseHelpers\FriendsDataResponse;
use App\Models\Profile;
use App\Models\UserQuiz;
use App\Models\OnlineTimeline;
use App\Models\User;
use Illuminate\Http\Request;
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
        $result->fullName = $this->user->profile->full_name;
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
        $result->walletBalance = $this->user->wallet->balance;
        $result->badge = $this->user->achievement;
        $result->winRate = $this->user->win_rate;
        $result->totalChallenges = $this->user->challenges_played;
        $result->boosts = $this->user->userBoosts();
        $result->achievements = $this->user->userAchievements();
        $result->recentGames = $this->user->recentGames();
        $result->transactions = $this->user->userTransactions(); //remove to wallet endpint
        $result->friends = $this->user->friends(); //remove to friends screen/endpoints
        $result->pointsTransaction = $this->user->getUserPointTransactions(); //remove to wallet endpint
        $result->hasActivePlan = $this->user->hasActivePlan();
        $result->activePlans = $this->composeUserPlans();

        return $this->sendResponse($result, 'User details');
    }


    public function quizzes()
    {
        $user = auth()->user();
        $quizzes = UserQuiz::where('user_id', $user->id)->get();

        return $this->sendResponse($quizzes, "User Quizzes");
    }

    public function searchFriends(Request $request)
    {
        // $user_array = array();

        if (!is_null($request)) {
            $search = $request['search'];
            $result = User::where('phone_number', 'like', '%' . $search . '%')
                ->orWhere('username', 'like', '%' . $search . '%')
                ->limit(10)->get()->map(function ($friend) {
                    $data = new stdClass;
                    $data->id = $friend->id;
                    //$data->fullName = $friend->profile->full_name;
                    $data->username = $friend->username;
                    $data->avatar = $friend->profile->avatar;
                    return $data;
                });

            // return $this->sendResponse($result, "Retrieved result for friends");
            return (new FriendsDataResponse())->transform(collect($result));
        }



        return $this->sendResponse([], "Retrieved result for friends");
    }



    public function friendQuizzes()
    {
        $user = auth()->user();
        $quizzes = [];
        $friends = Profile::where('referrer', $user->profile->referral_code)->get();

        foreach ($friends as $friend) {
            $quizzes[] = UserQuiz::where('user_id', $friend->id)->get();
        }
        return $this->sendResponse($quizzes, "Friends Quizzes");
    }

    public function setOnline()
    {
        $lastLoggedRecord = OnlineTimeline::where('user_id', $this->user->id)->first();

        if ($lastLoggedRecord !== null) {
            $lastLoggedRecord->update(['updated_at' => Carbon::now()]);
            return $this->sendResponse('Online status updated', "Online status updated");
        }

        OnlineTimeline::create([
            'user_id' => $this->user->id,
            'referrer' => $this->user->profile->referrer
        ]);
        return $this->sendResponse('Online status updated', "Online status updated");
    }

    private function composeUserPlans()
    {
        //get all the active plans the user has
        //If user has no active plan, return and empty array
        //If user has active plans, check if a user has bonus plans
        //if the user has bonus plans, check if user has expirable bonus plan
        // Add the number of expirable and non expirable bonus plans 

        //check if a user has purchased plans
        //if user has purchased plans, add the number of purchased plans

        //return user's bonus plans and user's purchased plans

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
}

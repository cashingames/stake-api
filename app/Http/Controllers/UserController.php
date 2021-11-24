<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use App\Models\GameType;
use App\Models\UserQuiz;
use App\Models\OnlineTimeline;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;

class UserController extends BaseController
{

    public function profile()
    {

        Log::info('Showing the user profile for user: ');

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
        $result->referralCode = $this->user->profile->referral_code;
        $result->points = $this->user->points();
        $result->globalRank = $this->user->rank;
        $result->gamesCount = $this->user->played_games_count;
        $result->walletBalance = $this->user->wallet->balance;
        $result->badge = $this->user->achievement;
        $result->winRate = $this->user->win_rate;
        $result->totalChallenges = $this->user->challenges_played;
        $result->boosts = $this->user->userBoosts();
        $result->achievements = $this->user->userAchievements();
        $result->recentGames = $this->user->recentGames();
        $result->transactions = $this->user->userTransactions();
        $result->friends = $this->user->friends();
        $result->pointsTransaction = $this->user->getUserPointTransactions();
        $result->hasActivePlan = $this->user->hasActivePlan();
        $result->activePlans = $this->user->active_plans;
        $result->hasPaidActivePlan = $this->user->hasPaidPlan();

        // $result->gamePerformance = 
        /**
         * 1.fetch userscore last weeks
         * 2.fetch userscore last two weeks
         * 
         * if( 1 > 2) then increase, % increase = (1-2)%. gamePermance = Your result was up X% last week. You can try harder this week.
         * else if( 2 > 1) then decrease % decrease = (2-1)% gamePermance = Oops, your result was X% down last week. You can try harder this week.
         * 
         * return 
         */

        return $this->sendResponse($result, 'User details');
    }


    public function quizzes()
    {
        $user = auth()->user();
        $quizzes = UserQuiz::where('user_id', $user->id)->get();

        return $this->sendResponse($quizzes, "User Quizzes");
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


}

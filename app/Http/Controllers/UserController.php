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
    //

    public function me()
    {
        try {
            $result = [
                'user' => $this->user->load([
                    'profile',
                    'wallet',
                    'transactions',
                    'boosts'
                ]),
            ];
            return $this->sendResponse($result, 'User details');
        } catch (\Exception $e) {
            error_log($e->getLine() . ', ' . $e->getMessage());
            return $this->sendError([], $e->getMessage());
        }
    }

    public function profile()
    {

        Log::info('Showing the user profile for user: ');

        $result = new stdClass;
        $result->username = $this->user->username;
        $result->email = $this->user->email;
        $result->lastName = $this->user->profile->last_name;
        $result->firstName = $this->user->profile->first_name;
        $result->fullName = $this->user->profile->first_name . " " . $this->user->profile->last_name;
        $result->phoneNumber = $this->user->phone_number;
        $result->bankName = $this->user->profile->bank_name;
        $result->accountName = $this->user->profile->account_name;
        $result->accountNumber = $this->user->profile->account_number;
        $result->dateOfBirth = $this->user->profile->date_of_birth;
        $result->gender = $this->user->profile->gender;
        $result->avatar = $this->user->profile->avatar;
        $result->referralCode = $this->user->profile->referral_code;
        $result->points = $this->user->points;
        $result->globalRank = $this->user->rank;
        $result->gamesCount = $this->user->played_games_count;
        $result->walletBalance = $this->user->wallet->balance;
        $result->badge = $this->user->achievement;
        $result->winRate = $this->user->win_rate;
        $result->totalChallenges = $this->user->challenges_played;
        $result->boosts = DB::table('user_boosts')->where('user_id', $this->user->id)
            ->join('boosts', function ($join) {
                $join->on('boosts.id', '=', 'user_boosts.boost_id');
            })->select('boosts.id', 'name', 'user_boosts.boost_count as count')->where('user_boosts.boost_count', '>', 0)->get();
        $result->achievements = DB::table('user_achievements')->where('user_id', $this->user->id)
            ->join('achievements', function ($join) {
                $join->on('achievements.id', '=', 'user_achievements.achievement_id');
            })->select('achievements.id', 'title', 'medal as logoUrl')->get();
        $result->recentGames = $this->user->gameSessions()->latest()->limit(3)->get()->map(function ($x) {
            return $x->category()->select('id', 'name', 'description', 'primary_color as bgColor', 'icon_name as icon')->first();
        });
        $result->transactions = $this->user->transactions()
            ->select('transaction_type as type', 'amount', 'description', 'wallet_transactions.created_at as transactionDate')
            ->orderBy('transactionDate', 'desc')
            ->get();
        $result->earnings = $this->user->transactions()
            ->where('transaction_type', 'Fund Recieved')
            ->orderBy('created_at', 'desc')->get();

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

    public function getPoints()
    {
        return $this->sendResponse($this->user->points, "User Points");
    }

    public function getPointsLog()
    {
        $pointsLog = $this->user->points()->latest()->get();
        return $this->sendResponse($pointsLog, "User Points Log");
    }

    public function getBoosts()
    {
        $userBoosts = DB::table('user_boosts')->where('user_id', $this->user->id)
            ->join('boosts', function ($join) {
                $join->on('boosts.id', '=', 'user_boosts.boost_id');
            })
            ->get();

        return $this->sendResponse($userBoosts, "User Boosts");
    }

    public function userAchievement()
    {
        $userId = $this->user->id;

        $userAchievement = DB::table('user_achievements')->where('user_id', $userId)
            ->join('achievements', function ($join) {
                $join->on('achievements.id', '=', 'user_achievements.achievement_id');
            })
            ->get();

        return $this->sendResponse($userAchievement, "User Achievement");
    }

    public function quizzes()
    {
        $user = auth()->user();
        $quizzes = UserQuiz::where('user_id', $user->id)->get();

        return $this->sendResponse($quizzes, "User Quizzes");
    }

    public function friends()
    {

        $friends = User::where('id','!=', $this->user->id)->get()->map(function ($user) {
            $data = new stdClass;
            $data->id = $user->id;
            $data->fullName = $user->profile->first_name . ' ' . $user->profile->last_name;
            $data->username = $user->username;
            $data->avatar = $user->profile->avatar;
            return $data;
        });

        return $this->sendResponse($friends, "Friends");
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

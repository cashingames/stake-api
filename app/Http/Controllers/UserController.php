<?php

namespace App\Http\Controllers;

use App\Models\GameType;
use App\Models\Profile;
use App\Models\User;
use App\Models\UserQuiz;
use App\Models\OnlineTimeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
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
        $result = new stdClass;
        $result->username = $this->user->username;
        $result->email = $this->user->email;
        $result->lastName = $this->user->profile->last_name;
        $result->firstName = $this->user->profile->first_name;
        $result->fullName = $this->user->profile->first_name . " " . $this->user->profile->last_name;
        $result->avatar = $this->user->profile->avatar;
        $result->points = $this->user->points;
        $result->globalRank = $this->user->rank;
        $result->gamesCount = $this->user->played_games_count;
        $result->walletBalance = $this->user->wallet->balance;
        $result->recentGames = $this->user->gameSessions()->latest()->limit(3)->get()->map(function ($x) {
            return $x->category()->select('id', 'name', 'description', 'primary_color as bgColor', 'icon_name as icon')->first();
        });
        $result->gameTypes = GameType::inRandomOrder()->select('name', 'description', 'icon', 'primary_color_2 as bgColor')
            ->get()->map(function ($item) {
                $item->isEnabled = $item->is_available;
                return $item;
            });

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

        $users = User::get();
        $onlineFriends = [];
        $offlineFriends = [];

        foreach ($users as $friend) {
            $isOnline = OnlineTimeline::where('user_id', $friend->id)
                ->where('updated_at', '>', Carbon::now()->subMinutes(5)->toDateTimeString())->first();
            if ($isOnline !== null) {
                $onlineFriends[] = $isOnline->user->load('profile');
            }
            $isOffline = OnlineTimeline::where('user_id', $friend->id)
                ->where('updated_at', '<', Carbon::now()->subMinutes(5)->toDateTimeString())->first();
            if ($isOffline !== null) {
                $offlineFriends[] = $isOffline->user->load('profile');
            }
        }

        $result = [
            'online' => $onlineFriends,
            'offline' => $offlineFriends
        ];
        return $this->sendResponse($result, "Friends");
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
        OnlineTimeline::create([
            'user_id' => $this->user->id,
            'referrer' => $this->user->profile->referrer
        ]);
        return $this->sendResponse('Online status updated', "Online status updated");
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile;
use App\Models\User;
use App\Models\UserQuiz;
use App\Models\OnlineTimeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class UserController extends BaseController
{
    //

    public function me()
    {
        try {
            $user = $this->user->load('profile');
            $result = [
                'user' => $user->load([
                    'profile',
                    'wallet',
                    'transactions',
                    'boosts']),
            ];
            return $this->sendResponse($result, 'User details');
        } catch(\Exception $e){
            error_log($e->getLine().', '.$e->getMessage());
            return $this->sendError([], $e->getMessage());
        }
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
       $userBoosts = DB::table('user_boosts')->where('user_id',$this->user->id)
        ->join('boosts', function ($join) {
            $join->on('boosts.id', '=', 'user_boosts.boost_id');
        })
        ->get();

        return $this->sendResponse($userBoosts, "User Boosts");
    }

    public function userAchievement()
    {
        $userId = $this->user->id;

        $userAchievement = DB::table('user_achievements')->where('user_id',$userId)
        ->join('achievements', function ($join) {
            $join->on('achievements.id', '=', 'user_achievements.achievement_id');
        })
        ->get();

        return $this->sendResponse($userAchievement, "User Achievement");
    }

    public function quizzes(){
        $user = auth()->user();
        $quizzes = UserQuiz::where('user_id',$user->id)->get();

        return $this->sendResponse($quizzes, "User Quizzes");
    }

    public function friends(){
        $user = auth()->user();
        $friends = Profile::where('referrer',$user->profile->referral_code)->get();

        if($friends === null){
            return $this->sendError("You have no friends yet", "You have no friends yet");
        }

        $isOnline = OnlineTimeline::where('referrer',$user->profile->referral_code)->where('updated_at', '>', Carbon::now()->subMinutes(5)->toDateTimeString())->get();
        
        $onlineFriends = [];
        foreach($isOnline as $friend){
            $details = $friend->user->load('profile');
            $onlineFriends[] = $details;   
        }

        $isOffline = OnlineTimeline::where('referrer',$user->profile->referral_code)->where('updated_at', '<', Carbon::now()->subMinutes(5)->toDateTimeString())->get();

        $offlineFriends = [];
        foreach($isOffline as $friend){
            $details = $friend->user->load('profile');
            $offlineFriends[] = $details;   
        }
        
        $result = [
            'online'=>$onlineFriends,
            'offline' =>$offlineFriends
        ];
        return $this->sendResponse($result, "Friends");
    }

    public function friendQuizzes(){
        $user = auth()->user();
        $quizzes = [];
        $friends = Profile::where('referrer',$user->profile->referral_code)->get();

        foreach($friends as $friend){
            $quizzes[]= UserQuiz::where('user_id',$friend->id)->get();
        }
        return $this->sendResponse($quizzes, "Friends Quizzes");
    }

    public function setOnline(){
        OnlineTimeline::create([
            'user_id' => $this->user->id,
            'referrer' => $this->user->profile->referrer
        ]);
        return $this->sendResponse('Online status updated', "Online status updated");
    }
}

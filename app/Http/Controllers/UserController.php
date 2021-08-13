<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile;
use App\Models\User;
use App\Models\UserQuiz;
use Illuminate\Support\Facades\DB;

class UserController extends BaseController
{
    //

    public function me()
    {
        $user = $this->user->load('profile');
        $result = [
            'user' => $user,
            'wallet' => $user->wallet->load("transactions"),
            'boosts' => $user->boosts
        ];
        return $this->sendResponse($result, 'User details');
    }

    public function getPoints($userId)
    {
        $user = User::find($userId);
        if($user==null){
            return $this->sendResponse("User not found", "User not found");
        }
        $points = $user->points;
        return $this->sendResponse($points, "User Points");
    }

    public function getPointsLog($userId)
    {
        $user = User::find($userId);
        if($user==null){
            return $this->sendResponse("User not found", "User not found");
        }
        $pointsLog = $user->points()->latest()->get();
        return $this->sendResponse($pointsLog, "User Points Log");
    }

    public function getBoosts($userId)
    {
        $user = User::find($userId);
        if($user==null){
            return $this->sendResponse("User not found", "User not found");
        }

       $userBoosts = DB::table('user_boosts')->where('user_id',$userId)
        ->join('boosts', function ($join) {
            $join->on('boosts.id', '=', 'user_boosts.boost_id');
        })
        ->get();

        return $this->sendResponse($userBoosts, "User Boosts");
    }

    public function quizzes(){
        $user = auth()->user();
        $quizzes = UserQuiz::where('user_id',$user->id)->get();

        return $this->sendResponse($quizzes, "User Quizzes");
    }

    public function friends(){
        $user = auth()->user();
        $friends = Profile::where('referrer',$user->profile->referral_code)->get();

        return $this->sendResponse($friends, "Friends");
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
}

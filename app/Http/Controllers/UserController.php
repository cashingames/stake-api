<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
            'wallet' => $user->wallet,
            'points' => $user->points,
            'boosts' => $user->boosts
        ];
        return $this->sendResponse($result, 'User details');
    }

    public function getPoints($id)
    {
        $user = User::find($id);
        if($user==null){
            return $this->sendResponse("User not found", "User not found");
        }
        $points = $user->points->sum('value');
        return $this->sendResponse($points, "User Points");
    }

    public function getBoosts($id)
    {
        $user = User::find($id);
        if($user==null){
            return $this->sendResponse("User not found", "User not found");
        }

       $userBoosts = DB::table('user_boosts')->where('user_id',$id)
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
        $friends = User::where('referrer',$user->profile->referral_code)->get();

        return $this->sendResponse($friends, "Friends");
    }

    public function friendQuizzes(){
        $user = auth()->user();
        $quizzes = [];
        $friends = User::where('referrer',$user->profile->referral_code)->get();

        foreach($friends as $friend){
            $quizzes[]= UserQuiz::where('user_id',$friend->id)->get();
        }
        return $this->sendResponse($quizzes, "Friends Quizzes");
    }
}

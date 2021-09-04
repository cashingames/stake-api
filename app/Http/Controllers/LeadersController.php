<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CategoryRanking;

class LeadersController extends BaseController
{
    //

    public function globalLeaders(){

        $leaders = User::orderBy('points', 'desc')->with('profile')->limit(50)->get();

        return $this->sendResponse($leaders, "Leaders");
    }

    public function categoryRankings($catId){
        $ranks = CategoryRanking::where('category_id', $catId)->orderBy('points_gained','desc')->get();

        return $this->sendResponse($ranks, 'category rankings');
    }
}

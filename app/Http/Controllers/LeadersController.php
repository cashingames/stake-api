<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CategoryRanking;

class LeadersController extends BaseController
{
    //

    public function globalLeaders()
    {

        $leaders = User::orderBy('points', 'desc')->with('profile')->limit(50)->get();

        return $this->sendResponse($leaders, "Leaders");
    }

    public function categoryRankings($catId)
    {
        $ranks = CategoryRanking::where('category_id', $catId)->orderBy('points_gained', 'desc')->get();

        return $this->sendResponse($ranks, 'category rankings');
    }

    /**
     * Returns just the global leaders
     * This is useful for dashboard where we only need global leaders
     */
    public function global()
    {
        $leaders = User::select('points', 'user_index_status')->orderBy('points', 'desc')->with('profile:first_name,last_name,avatar')->limit(25)->get();
        return $this->sendResponse($leaders, "Global Leaders");
    }

    /**
     * Return all leaders based on global and categories
     * This is useful for extended leaderboard where we need all the data
     */
    public function all()
    {
        return null;
    }

    /**
     * Returns all the leaders for the categories in the system
     * This can be useful in the future for anything else we need to implement
     */
    public function categories()
    {
    }

    /**
     * Returns all the leaders for all the recent categories the user has played
     * This can be useful in the future for anything else
     */
    public function recentCategories()
    {
    }
}

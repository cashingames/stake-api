<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CategoryRanking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
        $leaders = DB::table('users')
            ->orderBy('points', 'desc')
            ->join('profiles', 'users.id', '=', 'profiles.user_id')
            ->select('users.points', 'users.user_index_status', 'profiles.first_name', 'profiles.last_name', 'profiles.avatar')
            ->limit(25)->get();

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
        $response = [];
        Category::whereNull('category_id')->has('users')->get()->each(function ($item) use (&$response) {
            $board = $item->users()->orderBy('points_gained', 'desc')
                ->join('users', 'users.id', '=', 'user_id')
                ->join('profiles', 'users.id', '=', 'profiles.user_id')
                ->select('points_gained as points', 'users.user_index_status', 'profiles.first_name', 'profiles.last_name', 'profiles.avatar')
                ->limit(25)
                ->get();
            $response[$item->name] = $board;
        });
        return $this->sendResponse($response, 'Categories leaderboard');
    }

    /**
     * Returns all the leaders for all the recent categories the user has played
     * This can be useful in the future for anything else
     */
    public function recentCategories()
    {
    }
}

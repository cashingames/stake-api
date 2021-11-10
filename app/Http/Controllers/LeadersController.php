<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Facades\DB;

class LeadersController extends BaseController
{
    /**
     * Returns just the global leaders
     * This is useful for dashboard where we only need global leaders
     */
    public function global()
    {
        $leaders = DB::select(
            'SELECT g.*, p.avatar, p.first_name , p.last_name
            FROM (
                SELECT SUM(points_gained) AS points, user_id FROM game_sessions
                INNER JOIN users ON users.id = game_sessions.user_id
                GROUP BY user_id
                ORDER BY points DESC
                LIMIT 25
            ) g
            INNER JOIN profiles p ON g.user_id = p.user_id
            ORDER BY g.points DESC'
        );  

        return $this->sendResponse($leaders, "Global Leaders");
    }

    /**
     * Returns all the leaders for the categories in the system
     * This can be useful in the future for anything else we need to implement
     */
    public function categories()
    {
        $response = [];
        Category::where('category_id', 0)->has('users')->get()->each(function ($item) use (&$response) {
            $board = $item->users()->orderBy('points_gained', 'desc')
                ->join('users', 'users.id', '=', 'game_sessions.user_id')
                ->join('profiles', 'users.id', '=', 'profiles.user_id')
                ->select('points_gained as points', 'profiles.first_name', 'profiles.last_name', 'profiles.avatar')
                ->limit(25)
                ->get();
            $response[$item->name] = $board;
        });
        return $this->sendResponse($response, 'Categories leaderboard');
    }
}

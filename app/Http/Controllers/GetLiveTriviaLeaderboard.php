<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GetLiveTriviaLeaderboard extends Controller
{

    public function __invoke($id)
    {
        //get trivia leaders

        $query = 'SELECT r.points, p.first_name , p.last_name, p.user_id
        FROM (
            SELECT SUM(points_gained) AS points, user_id, username 
            FROM game_sessions gs
            INNER JOIN users ON users.id = gs.user_id 
            WHERE gs.trivia_id = ? 
            GROUP BY gs.user_id
        ) r
        JOIN profiles p ON p.user_id = r.user_id
        ORDER BY points DESC ';

        $leaders = DB::select($query, [$id]);

        return $this->sendResponse($leaders, "Live trivia leaderboard");
    }
}

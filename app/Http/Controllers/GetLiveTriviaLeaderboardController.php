<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\LiveTrivia;
use App\Http\ResponseHelpers\LiveTriviaStatusResponse;

class GetLiveTriviaLeaderboardController extends Controller
{

    public function __invoke($id)
    {
        //get trivia leaders

        $query = 'SELECT r.points, r.username, p.first_name , p.last_name, p.user_id
        FROM (
            SELECT SUM(points_gained) AS points, start_time AS startTime, end_time As endTime, user_id, username 
            FROM game_sessions gs
            INNER JOIN users ON users.id = gs.user_id 
            WHERE gs.trivia_id = ? 
            GROUP BY gs.user_id
        ) r
        JOIN profiles p ON p.user_id = r.user_id
        ORDER BY points DESC ';

        $leaders = DB::select($query, [$id]);

        return response()->json($leaders);
    }

}

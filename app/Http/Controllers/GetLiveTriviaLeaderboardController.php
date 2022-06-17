<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\ResponseHelpers\LiveTriviaLeaderboardResponse;

class GetLiveTriviaLeaderboardController extends Controller
{

    public function __invoke($id)
    {
        $query = 'SELECT r.points, r.username, r.startTime, r.endTime, p.user_id, p.avatar
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
        
        return (new LiveTriviaLeaderboardResponse())->transform(collect($leaders));
    }

}

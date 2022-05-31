<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Trivia;
use App\Models\TriviaQuestion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TriviaController extends BaseController
{
    public function getTrivia()
    {
        $trivia = Trivia::where('is_published', true)->orderBy('created_at', 'DESC')->limit(10)->get();
        return $this->sendResponse($trivia, "Triva");
    }

    public function getLiveTriviaLeaderboard($triviaId)
    {
        //get trivia leaders

        $query = 'SELECT r.points, p.first_name , p.last_name, p.user_id
        FROM (
            SELECT SUM(points_gained) AS points, user_id, username 
            FROM game_sessions gs
            INNER JOIN users ON users.id = gs.user_id 
            WHERE gs.trivia_id = ? 
            GROUP BY gs.user_id
            ORDER BY points DESC 
        ) r
        JOIN profiles p ON p.user_id = r.user_id';

        $leaders = DB::select($query, [$triviaId]);

        $data = [
            'leaders' => $leaders,
        ];

        return $this->sendResponse($data, "Live trivia leaderboard");
    }
}

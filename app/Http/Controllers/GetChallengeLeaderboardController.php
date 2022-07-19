<?php

namespace App\Http\Controllers;

use App\Http\ResponseHelpers\ChallengeLeaderboardResponse;
use Illuminate\Support\Facades\DB;

class GetChallengeLeaderboardController extends Controller
{

    public function __invoke($challengeId)
    {


       $query = 'SELECT u.username, up.avatar, op.avatar as opponentAvatar, o.username as opponentUsername, 
        sum(cgsu.points_gained) as challengerPoint, 
        sum(cgso.points_gained) as opponentPoint,
        TIMESTAMPDIFF(SECOND, cgsu.start_time, cgsu.end_time) AS challengerFinishduration,
        TIMESTAMPDIFF(SECOND, cgso.start_time, cgso.end_time) AS opponentFinishduration,
        cgsu.state as challengerStatus, cgso.state as opponentStatus, challenges.created_at 
        FROM challenges
        INNER JOIN challenge_game_sessions cgsu on cgsu.user_id = challenges.user_id
        LEFT JOIN challenge_game_sessions cgso on cgso.user_id = challenges.opponent_id
        INNER JOIN users u on u.id = challenges.user_id
        INNER JOIN users o on o.id = challenges.opponent_id
        INNER JOIN profiles up on up.id = challenges.user_id
        INNER JOIN profiles op on op.id = challenges.opponent_id
        WHERE challenges.id = ?';
        $result = DB::select($query, [$challengeId]);
       
        return (new ChallengeLeaderboardResponse())->transform(collect($result));
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\ResponseHelpers\ChallengeGlobalLeadersResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ChallengeGlobalLeadersController extends BaseController
{
    public function __invoke(Request $request)
    {
        $_startDate = $this->toNigeriaTimeZoneFromUtc(Carbon::today()->startOfDay());

        $_endDate = $this->toNigeriaTimeZoneFromUtc(Carbon::today()->endOfDay());
        $limit = 3;

        if ($request->has(['startDate', 'endDate'])) {

            $_startDate = $this->toNigeriaTimeZoneFromUtc(Carbon::parse($request->startDate)->startOfDay());
            $_endDate = $this->toNigeriaTimeZoneFromUtc(Carbon::parse($request->endDate)->endOfDay());
        }
        if ($request->has(['limit'])) {
            $limit = $request->limit;
        }
        $sql = "select winner, count(winner) as wins, CASE WHEN (winner = user_id) THEN challengerAvatar ELSE 
                (CASE WHEN (winner = opponent_id) THEN opponentAvatar END) END as avatar,
                CASE WHEN (winner = user_id) THEN challengerUsername ELSE 
                (CASE WHEN (winner = opponent_id) THEN opponentUsername END) END as username
                from (select c.id, c.user_id, c.opponent_id, cgsC.points_gained challengerScore, cgsO.points_gained opponentScore, 
                CASE WHEN (cgsC.points_gained - cgsO.points_gained) > 0 THEN c.user_id ELSE 
                    (CASE WHEN (cgsC.points_gained - cgsO.points_gained) = 0 THEN null ELSE c.opponent_id END) END as winner, cp.avatar as challengerAvatar, op.avatar as opponentAvatar,
                    cu.username as challengerUsername, co.username as opponentUsername
                from challenges c
                inner join profiles cp ON c.user_id = cp.user_id 
                inner join profiles op ON c.opponent_id = op.user_id 
                inner join users cu ON c.user_id = cu.id 
                inner join users co ON c.opponent_id = co.id 
                inner join challenge_game_sessions cgsC on c.user_id = cgsC.user_id and c.id = cgsC.challenge_id
                inner join challenge_game_sessions cgsO on c.opponent_id = cgsO.user_id and c.id = cgsO.challenge_id
    
                where c.status = 'ACCEPTED' AND c.created_at >= ? AND c.created_at < ?) leaderboard
                where winner is not null
                group by leaderboard.winner
                order by count(winner) desc limit ?";
        
        Cache::remember('challenge-leaders', 3, function () use ($sql, $_startDate, $_endDate, $limit) {
            return DB::select($sql, [$_startDate, $_endDate, $limit]);
        });
        
        return (new ChallengeGlobalLeadersResponse())->transform(Cache::get('challenge-leaders'));
           
    }
}

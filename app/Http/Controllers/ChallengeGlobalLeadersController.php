<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ChallengeGlobalLeadersController extends BaseController
{
    public function __invoke(Request $request, $limit)
    {
        $_startDate = $this->toNigeriaTimeZoneFromUtc(Carbon::today()->subDays(7));
        $_endDate = $this->toNigeriaTimeZoneFromUtc(Carbon::today()->endOfDay());
        $limit = 3;

        if ($request->has(['startDate', 'endDate'])) {

            $_startDate = $this->toNigeriaTimeZoneFromUtc(Carbon::parse($request->startDate)->startOfDay());
            $_endDate = $this->toNigeriaTimeZoneFromUtc(Carbon::parse($request->endDate)->endOfDay());
        }
        if ($request->has(['limit'])) {
            $limit = $request->limit;
        }
        $sql = 'SELECT c.points, p.avatar, p.first_name , p.last_name, c.username
            FROM (
                SELECT SUM(points_gained) AS points, user_id, username FROM challenge_game_sessions cs
                INNER JOIN users ON users.id = cs.user_id WHERE cs.created_at >= ? AND cs.created_at < ?  GROUP BY user_id
                ORDER BY points DESC
                LIMIT ?
            ) c
            INNER JOIN profiles p ON g.user_id = p.user_id
            ORDER BY c.points DESC';


        $leaders = DB::select($sql, [$this->toUtcFromNigeriaTimeZone($_startDate),  $this->toUtcFromNigeriaTimeZone($_endDate), $limit]);


        return $this->sendResponse($leaders, "Challenge Leaders");
    }
}

<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Traits\Utils\DateUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GetGlobalLeaderboardController extends BaseController
{
    use DateUtils;
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {

        $_startDate = $this->toNigeriaTimeZoneFromUtc(Carbon::today()->startOfDay());
        $_endDate = $this->toNigeriaTimeZoneFromUtc(Carbon::today()->endOfDay());

        if ($request->has(['startDate', 'endDate'])) {
            $_startDate = $this->toNigeriaTimeZoneFromUtc(Carbon::parse($request->startDate)->startOfDay());
            $_endDate = $this->toNigeriaTimeZoneFromUtc(Carbon::parse($request->endDate)->endOfDay());
        }

        $sql = 'SELECT g.points, p.avatar, p.first_name , p.last_name, g.username
                FROM (
                    SELECT SUM(points_gained) AS points, user_id, username FROM game_sessions gs
                    INNER JOIN users ON users.id = gs.user_id WHERE gs.created_at >= ? AND gs.created_at < ?  GROUP BY user_id
                    ORDER BY points DESC
                    LIMIT 100
                ) g
                INNER JOIN profiles p ON g.user_id = p.user_id
                ORDER BY g.points DESC';
                
        $leaders = DB::select($sql, [
            $this->toUtcFromNigeriaTimeZone($_startDate),
            $this->toUtcFromNigeriaTimeZone($_endDate)
        ]);
        $userRank = new \stdClass();
        $userRank->points = -1;

        foreach ($leaders as $key => $value){
            if ($value->username == $this->user->username) {
                $userRank->points = $value->points;
                $userRank->rank = $key + 1;

                break;
        }

        if($userRank->points == -1){
            $userRank->points = 0;
            $userRank->rank = rand(101, 150);
        }
       
        $result = [
            "leaderboard" => $leaders,
            "userRank" => $userRank
        ];

        return $this->sendResponse($result, "Leaderboard");
    }
}   
}
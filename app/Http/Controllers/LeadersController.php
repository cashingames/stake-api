<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class LeadersController extends BaseController
{
    /**
     * Returns just the global leaders
     * This is useful for dashboard where we only need global leaders
     */
    public function global(Request $request)
    {
        $filter = false;
        $_startDate = null;
        $_endDate = null;
        if ($request->has(['startDate','endDate'])) {
            $_startDate = Carbon::parse($request->startDate)->startOfDay('Africa/Lagos');
            $_endDate = Carbon::parse($request->endDate)->tomorrow('Africa/Lagos');
            $filter = true;
        }
        else {
            $_startDate = Carbon::today()->startOfDay('Africa/Lagos');
            $_endDate = Carbon::tomorrow()->startOfDay('Africa/Lagos');
            $filter = true;
        }

        $sql = 'SELECT g.points, p.avatar, p.first_name , p.last_name, g.username
            FROM (
                SELECT SUM(points_gained) AS points, user_id, username FROM game_sessions gs
                INNER JOIN users ON users.id = gs.user_id';

        $sql .= $filter ? ' WHERE gs.created_at >= ? AND gs.created_at < ?' : '';

        $sql .= ' GROUP BY user_id
                ORDER BY points DESC
                LIMIT 25
            ) g
            INNER JOIN profiles p ON g.user_id = p.user_id
            ORDER BY g.points DESC';

        if ($filter) {
            $leaders = DB::select($sql, [$_startDate,  $_endDate]);
        } else {
            $leaders = DB::select($sql);
        }

        return $this->sendResponse($leaders, "Global Leaders");
    }

    /**
     * Returns all the leaders for the categories in the system
     * This can be useful in the future for anything else we need to implement
     */
    public function categories(Request $request)
    {
        $filter = false;
        $_startDate = null;
        $_endDate = null;

        if ($request->has(['startDate','endDate'])) {
            $_startDate = Carbon::parse($request->startDate)->startOfDay('Africa/Lagos');
            $_endDate = Carbon::parse($request->endDate)->tomorrow('Africa/Lagos');
            $filter = true;
        } else {
            $_startDate = Carbon::today()->startOfDay('Africa/Lagos');
            $_endDate = Carbon::tomorrow()->startOfDay('Africa/Lagos');
            $filter = true;
        }

        $sql = 'SELECT p.avatar, p.first_name, p.last_name, r.points, c.id as category_id, c.name as category_name, r.username
            FROM 
            (SELECT sum(points_gained) points, gs.user_id, c.category_id, u.username
                FROM game_sessions gs
                INNER JOIN categories c on c.id = gs.category_id
                INNER JOIN users u on u.id = gs.user_id';

        $sql .= $filter ? ' WHERE gs.created_at >= ? AND gs.created_at < ?' : '';

        $sql .= ' GROUP BY c.category_id, gs.user_id
                ORDER BY points desc 
                limit 25
            ) r
            join profiles p on p.user_id = r.user_id
            join categories c on c.id = r.category_id
            ORDER BY r.points desc';

        if ($filter) {
            $leaders = DB::select($sql, [$_startDate,  $_endDate]);
        } else {
            $leaders = DB::select($sql);
        }

        $response = [];
        foreach ($leaders as $leader) {
            $response[$leader->category_name][] = $leader;
        }
        return $this->sendResponse($response, 'Categories leaderboard');
    }
}

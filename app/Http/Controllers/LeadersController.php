<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Traits\Utils\DateUtils;

class LeadersController extends BaseController
{
    use DateUtils;
    /**
     * Returns just the global leaders
     * This is useful for dashboard where we only need global leaders
     */
    public function global($startDate = null, $endDate = null)
    {
        $filter = false;
        $_startDate = null;
        $_endDate = null;
        if ($startDate && $endDate) {
            $_startDate = Carbon::createFromTimestamp($startDate)->startOfDay();
            $_endDate = Carbon::createFromTimestamp($endDate)->tomorrow();
            $filter = true;
        } else {
            $_startDate = Carbon::today()->startOfDay();
            $_endDate = Carbon::tomorrow()->startOfDay();
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
    public function categories($startDate = null, $endDate = null)
    {
        $filter = false;
        $_startDate = null;
        $_endDate = null;
        if ($startDate && $endDate) {
            $_startDate = Carbon::createFromTimestamp($startDate)->startOfDay();
            $_endDate = Carbon::createFromTimestamp($endDate)->tomorrow();
            $filter = true;
        } else {
            $_startDate = Carbon::today()->startOfDay();
            $_endDate = Carbon::tomorrow()->startOfDay();
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

    public function globalLeaders(Request $request)
    {
        $_startDate = $this->toNigeriaTimeZoneFromUtc(Carbon::today()->startOfDay());
        $_endDate = $this->toNigeriaTimeZoneFromUtc(Carbon::tomorrow()->startOfDay());

        if ($request->has(['startDate', 'endDate'])) {
            $_startDate = $this->toNigeriaTimeZoneFromUtc(Carbon::parse($request->startDate)->startOfDay());
            $_endDate = $this->toNigeriaTimeZoneFromUtc(Carbon::parse($request->endDate)->tomorrow());
        }
        $sql = 'SELECT g.points, p.avatar, p.first_name , p.last_name, g.username
            FROM (
                SELECT SUM(points_gained) AS points, user_id, username FROM game_sessions gs
                INNER JOIN users ON users.id = gs.user_id WHERE gs.created_at >= ? AND gs.created_at < ?  GROUP BY user_id
                ORDER BY points DESC
                LIMIT 25
            ) g
            INNER JOIN profiles p ON g.user_id = p.user_id
            ORDER BY g.points DESC';


        $leaders = DB::select($sql, [$this->toUtcFromNigeriaTimeZone($_startDate),  $this->toUtcFromNigeriaTimeZone($_endDate)]);


        return $this->sendResponse($leaders, "Global Leaders");
    }

    /**
     * Returns all the leaders for the categories in the system
     * This can be useful in the future for anything else we need to implement
     */
    public function categoriesLeaders(Request $request)
    {
        $_startDate = $this->toNigeriaTimeZoneFromUtc(Carbon::today()->startOfDay());
        $_endDate = $this->toNigeriaTimeZoneFromUtc(Carbon::tomorrow()->startOfDay());

        if ($request->has(['startDate', 'endDate'])) {
            $_startDate = $this->toNigeriaTimeZoneFromUtc(Carbon::parse($request->startDate)->startOfDay());
            $_endDate = $this->toNigeriaTimeZoneFromUtc(Carbon::parse($request->endDate)->tomorrow());
        }

        $sql = 'SELECT p.avatar, p.first_name, p.last_name, r.points, c.id as category_id, c.name as category_name, r.username
            FROM 
            (SELECT sum(points_gained) points, gs.user_id, c.category_id, u.username
                FROM game_sessions gs
                INNER JOIN categories c on c.id = gs.category_id
                INNER JOIN users u on u.id = gs.user_id WHERE gs.created_at >= ? AND gs.created_at < ? GROUP BY c.category_id, gs.user_id
                ORDER BY points desc 
                limit 25
            ) r
            join profiles p on p.user_id = r.user_id
            join categories c on c.id = r.category_id
            ORDER BY r.points desc';

        $leaders = DB::select($sql, [$this->toUtcFromNigeriaTimeZone($_startDate),  $this->toUtcFromNigeriaTimeZone($_endDate)]);


        $response = [];
        foreach ($leaders as $leader) {
            $response[$leader->category_name][] = $leader;
        }
        return $this->sendResponse($response, 'Categories leaderboard');
    }
}

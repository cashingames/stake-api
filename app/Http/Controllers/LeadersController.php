<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Facades\DB;

class LeadersController extends BaseController
{
    /**
     * Returns just the global leaders
     * This is useful for dashboard where we only need global leaders
     */
    public function global()
    {
        $leaders = DB::select(
            'SELECT g.points, p.avatar, p.first_name , p.last_name
            FROM (
                SELECT SUM(points_gained) AS points, user_id FROM game_sessions
                INNER JOIN users ON users.id = game_sessions.user_id
                WHERE DATE(gs.created_at) = CURDATE()
                GROUP BY user_id
                ORDER BY points DESC
                LIMIT 25
            ) g
            INNER JOIN profiles p ON g.user_id = p.user_id
            ORDER BY g.points DESC'

        );

        return $this->sendResponse($leaders, "Global Leaders");
    }

    /**
     * Returns all the leaders for the categories in the system
     * This can be useful in the future for anything else we need to implement
     */
    public function categories()
    {
        $response = [];
        $leaders = DB::select(
            'select p.avatar, p.first_name, p.last_name, r.points, c.id as category_id, c.name as category_name
            from 
            (select sum(points_gained) points, gs.user_id, c.category_id
                from game_sessions gs
                inner join categories c on c.id = gs.category_id
                inner join users u on u.id = gs.user_id
                WHERE DATE(gs.created_at) = CURDATE()
                group by c.category_id, gs.user_id
                order by points desc 
                limit 25
            ) r
            join profiles p on p.user_id = r.user_id
            join categories c on c.id = r.category_id
            order by r.points desc'
        );
        foreach ($leaders as $leader) {
            $response[$leader->category_name][] = $leader;
        }
        return $this->sendResponse($response, 'Categories leaderboard');
    }
}

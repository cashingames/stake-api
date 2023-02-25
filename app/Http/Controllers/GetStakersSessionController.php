<?php

namespace App\Http\Controllers;

use App\Http\ResponseHelpers\RecentStakersResponse;
use Illuminate\Support\Facades\DB;

class GetStakersSessionController extends BaseController
{
    public function __invoke()
    {
        $sessions = DB::table('exhibition_stakings')
            ->join('stakings', 'stakings.id', '=', 'exhibition_stakings.staking_id')
            ->join('game_sessions', 'game_sessions.id', '=', 'exhibition_stakings.game_session_id')
            ->join('users', 'users.id', '=', 'game_sessions.user_id')
            ->leftJoin('profiles', 'profiles.user_id', '=', 'users.id')
            ->select(
                'stakings.id as id',
                'users.username as username',
                'profiles.avatar as avatar',
                'stakings.amount_won as amount_won',
                'stakings.amount_staked as amount_staked',
                'game_sessions.correct_count as correct_count',
                'stakings.created_at as created_at',
            )
            ->whereColumn('stakings.amount_won', '>', 'stakings.amount_staked')
            ->groupBy('users.id')
            ->orderByDesc('game_sessions.created_at')
            ->orderByDesc('game_sessions.correct_count')
            ->orderByDesc('stakings.amount_won')
            ->limit(10)
            ->get();

        return $sessions->map(function ($session) {
            return (new RecentStakersResponse())->transform($session);
        });
    }
}
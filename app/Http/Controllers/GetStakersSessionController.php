<?php

namespace App\Http\Controllers;

use App\Http\ResponseHelpers\GameSessionResponse;
use App\Http\ResponseHelpers\RecentStakersResponse;
use App\Models\GameSession;
use App\Models\Staking;
use Illuminate\Http\Request;

class GetStakersSessionController extends BaseController
{
    public function __invoke()
    {
        $sessions = Staking::whereHas('exhibitionStakings')
            ->join('users', 'users.id', '=', 'stakings.user_id')
            ->join('profiles', 'profiles.user_id', '=', 'users.id')
            ->join('exhibition_stakings', 'stakings.id', '=', 'exhibition_stakings.staking_id')
            ->join('game_sessions', 'exhibition_stakings.game_session_id', '=', 'game_sessions.id')
            ->select(
                'stakings.id',
                'stakings.amount_won',
                'stakings.amount_staked',
                'users.username',
                'profiles.avatar',
                'game_sessions.correct_count',
                'stakings.created_at'
            )
            ->whereColumn('stakings.amount_won', '>', 'stakings.amount_staked')
            ->where('game_sessions.points_gained', '>=', 5)
            ->orderBy('game_sessions.created_at', 'DESC')
            ->orderBy('stakings.amount_won', 'DESC')
            ->groupBy('stakings.user_id')
            ->limit(10)->get();

        return $sessions->map(function ($session) {
            return (new RecentStakersResponse())->transform($session);
        });
    }
}

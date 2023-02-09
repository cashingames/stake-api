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
        $sessions = Staking::whereHas('exhibitionStakings')->with(['user', 'user.profile'])
            ->join('exhibition_stakings', 'stakings.id', '=', 'exhibition_stakings.staking_id')
            ->join('game_sessions', 'exhibition_stakings.game_session_id', '=', 'game_sessions.id')
            ->where('game_sessions.points_gained', '>=', 5)
            ->whereColumn('stakings.amount_won', '>', 'stakings.amount_staked')
            ->orderBy('stakings.amount_won', 'DESC')
            ->orderBy('stakings.created_at', 'ASC')
            ->groupBy('stakings.user_id')
            ->limit(10)->get();
        $data = [];


        foreach ($sessions as $session) {
            $data[] = (new RecentStakersResponse())->transform($session);
        }
        return $data;
    }
}

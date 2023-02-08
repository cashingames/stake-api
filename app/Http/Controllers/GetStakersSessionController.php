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
        $sessions = GameSession::whereHas('exhibitionStakings')->with(['exhibitionStakings','user', 'user.profile'])
            ->where('points_gained', '>=', 5)
            ->orderBy('amount_won', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->limit(10)->get();
        $data = [];

        // return $sessions;

        foreach ($sessions as $session) {
            $data[] = (new RecentStakersResponse())->transform($session);
        }
        return $data;

        //return $this->sendResponse($sessions, "Global Leaders");
    }
}

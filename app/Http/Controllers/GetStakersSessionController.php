<?php

namespace App\Http\Controllers;

use App\Http\ResponseHelpers\GameSessionResponse;
use App\Models\GameSession;
use App\Models\Staking;
use Illuminate\Http\Request;

class GetStakersSessionController extends BaseController
{
    public function __invoke()
    {
        $sessions = GameSession::whereHas('exhibitionStakings')->where('points_gained','>=', 5)->latest()->limit(10)->get();
        $data = [];

        foreach($sessions as $session){
            $data[]= (new GameSessionResponse())->transform($session);
        }
        return $data;
    }
}

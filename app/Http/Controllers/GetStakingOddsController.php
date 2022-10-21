<?php

namespace App\Http\Controllers;

use App\Models\StakingOdd;
use App\Services\StakingOddsComputer;
use Illuminate\Http\Request;

class GetStakingOddsController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $stakingOdds = StakingOdd::active()->orderBy('score', 'DESC')->get();
        $allStakingOddsWithOddsMultiplierApplied = [];
        $oddMultiplierComputer = new StakingOddsComputer();
        $oddMultiplier = $oddMultiplierComputer->compute($this->user, $this->user->getAverageOfLastThreeGames());

        foreach($stakingOdds as $odd){
            $odd->odd = round(($odd->odd * $oddMultiplier['oddsMultiplier']),2);
            $allStakingOddsWithOddsMultiplierApplied[]=$odd;
        }

        return $this->sendResponse($allStakingOddsWithOddsMultiplierApplied, 'staking odds fetched');
    }
}

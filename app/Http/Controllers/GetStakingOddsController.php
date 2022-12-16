<?php

namespace App\Http\Controllers;

use App\Enums\FeatureFlags;
use App\Models\StakingOdd;
use App\Services\FeatureFlag;
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
        $result = [];
        $message = 'staking odds fetched';

        if (!FeatureFlag::isEnabled(FeatureFlags::STAKING_WITH_ODDS)) {
            return $this->sendResponse($result, $message);
        }

        $stakingOdds = StakingOdd::active()->orderBy('score', 'DESC')->get();
        if ($stakingOdds->isEmpty()) {
            return $this->sendResponse($result, $message);
        }

        $allStakingOddsWithOddsMultiplierApplied = [];
        $oddMultiplierComputer = new StakingOddsComputer();
        $oddMultiplier = $oddMultiplierComputer->compute($this->user, $this->user->getAverageStakingScore());

        foreach ($stakingOdds as $odd) {
            $odd->odd = round(($odd->odd * $oddMultiplier['oddsMultiplier']), 2);
            $allStakingOddsWithOddsMultiplierApplied[] = $odd;
        }

        return $this->sendResponse($allStakingOddsWithOddsMultiplierApplied, $message);
       
    }
}

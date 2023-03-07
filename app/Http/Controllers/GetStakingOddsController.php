<?php

namespace App\Http\Controllers;

use App\Enums\FeatureFlags;
use App\Models\StakingOdd;
use App\Services\FeatureFlag;
use App\Services\StakingOddsComputer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class GetStakingOddsController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(StakingOddsComputer $oddsComputer): JsonResponse
    {
        $message = 'staking odds fetched';

        $stakingOdds = Cache::remember(
            'staking-odds',
            60 * 60,
            fn() => StakingOdd::active()->orderBy('score', 'DESC')->get()
        );

        /**
         * This controls the dynamic odd feature
         * @TODO Rename to TRIVIA_STAKING_WITH_DYNAMIC_ODDS
         */
        if (!FeatureFlag::isEnabled(FeatureFlags::STAKING_WITH_ODDS)) {
            return $this->sendResponse($stakingOdds, $message);
        }

        $allStakingOddsWithOddsMultiplierApplied = [];
        $oddMultiplier = $oddsComputer->compute($this->user);

        foreach ($stakingOdds as $odd) {
            $odd->odd = round(($odd->odd * $oddMultiplier['oddsMultiplier']), 2);
            $allStakingOddsWithOddsMultiplierApplied[] = $odd;
        }

        return $this->sendResponse($allStakingOddsWithOddsMultiplierApplied, $message);

    }
}

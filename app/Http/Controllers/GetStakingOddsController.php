<?php

namespace App\Http\Controllers;

use App\Enums\FeatureFlags;
use App\Services\FeatureFlag;
use App\Services\TriviaStaking\OddsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class GetStakingOddsController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(OddsService $oddsService): JsonResponse
    {
        $message = 'staking odds fetched';

        $result = $oddsService->getOdds($this->user);

        Log::info(
            'GET_ODDS_COMPUTED',
            [
                'user' => auth()->user()->username,
                'staking_with_odds' => FeatureFlag::isEnabled(FeatureFlags::STAKING_WITH_ODDS),
                'staking_session_odds' => $result
            ]
        );

        return $this->sendResponse($result, $message);

    }
}

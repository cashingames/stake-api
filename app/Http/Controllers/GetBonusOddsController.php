<?php

namespace App\Http\Controllers;

use App\Enums\FeatureFlags;
use App\Services\FeatureFlag;
use App\Services\TriviaStaking\OddsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class GetBonusOddsController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(): JsonResponse
    {
        $message = 'bonus odds fetched';
        $bonusOdds = config('bonusOdds');

        Log::info(
            'GET_ODDS_COMPUTED',
            $bonusOdds
        );

        return $this->sendResponse($bonusOdds, $message);

    }
}

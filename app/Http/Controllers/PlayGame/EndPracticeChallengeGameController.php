<?php

namespace App\Http\Controllers\PlayGame;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\ResponseHelpers\ResponseHelper;
use App\Services\PlayGame\StakingChallengeGameService;
use Illuminate\Support\Facades\Log;
class EndPracticeChallengeGameController extends Controller
{
    public function __invoke(
        Request $request,
        StakingChallengeGameService $triviaChallengeService,
    ): JsonResponse {

        $data = $request->validate([
            'challenge_request_id' => ['required'],
            'selected_options' => ['nullable'],
        ]);

        Log::info('EndPracticeChallengeGameController', $data);
        $result = $triviaChallengeService->submitPracticeChallenge($data);
        if (!$result) {
            Log::error('PRACTICE_CHALLENGE_SUBMIT_ERROR');
            return ResponseHelper::error('Unable to submit practice challenge');
        }

        return ResponseHelper::success((object) ['score' => $result->score]);
    }
}

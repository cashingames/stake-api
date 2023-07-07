<?php

namespace App\Http\Controllers\PlayGame;

use Illuminate\Http\Request;
use App\Models\ChallengeRequest;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\ResponseHelpers\ResponseHelper;
use Illuminate\Support\Facades\Log;
use App\Jobs\MatchWithBotChallengeRequest;
use App\Services\PlayGame\StakingChallengeGameService;

// @TODO change to single player logic to avoid firebase
class StartPracticeChallengeRequestController extends Controller
{
    public function __invoke(
        Request $request,
        StakingChallengeGameService $triviaChallengeService,
    ): JsonResponse {
        
        $data = $request->validate([
            'category' => ['required', 'numeric'],
            'amount' => ['required', 'numeric'],
        ]);

        Log::info('START_PRACTICE_CHALLENGE_REQUEST_PROCESS', [
            'user' => auth()->user()->username,
            'validatedRequest' => $data,
        ]);

        $result = $triviaChallengeService->createPracticeRequest($request->user(), $data);

        MatchWithBotChallengeRequest::dispatchSync($result, $request->header('x-request-env'));
       
        return ResponseHelper::success($this->transformResponse($result));
    }

    private function transformResponse(ChallengeRequest $challengeRequest): object
    {
        return (object) [
            'challenge_request_id' => 'trivia-challenge-requests/' . $challengeRequest->challenge_request_id
        ];
    }
}

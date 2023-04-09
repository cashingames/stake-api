<?php

namespace App\Http\Controllers\PlayGame;

use Illuminate\Http\Request;
use App\Models\ChallengeRequest;
use Illuminate\Http\JsonResponse;
use App\Jobs\MatchChallengeRequest;
use App\Http\Controllers\Controller;
use App\Http\ResponseHelpers\ResponseHelper;
use App\Services\PlayGame\StakingChallengeGameService;

class StartChallengeRequestController extends Controller
{
    public function __invoke(
        Request $request,
        StakingChallengeGameService $triviaChallengeService,
    ): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'category' => ['required', 'numeric', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'max:' . $user->wallet->non_withdrawable_balance],
        ]);

        $result = $triviaChallengeService->create($user, $data);

        MatchChallengeRequest::dispatch($result);

        return ResponseHelper::success($this->transformResponse($result));
    }

    private function transformResponse(ChallengeRequest $challengeRequest): object
    {
        return (object) [
            'challenge_request_id' => 'trivia-challenge-requests/' . $challengeRequest->challenge_request_id
        ];
    }
}

<?php

namespace App\Http\Controllers\PlayGame;

use App\Models\ChallengeRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\ResponseHelpers\ResponseHelper;
use App\Actions\TriviaChallenge\MatchRequestAction;
use App\Services\PlayGame\StakingChallengeGameService;

class StartChallengeRequestController extends Controller
{
    public function __invoke(
        Request $request,
        StakingChallengeGameService $triviaChallengeService,
        MatchRequestAction $matchAction
    ): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'category' => ['required', 'numeric', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'max:' . $user->wallet->non_withdrawable_balance],
        ]);

        $result = $triviaChallengeService->create($user, $data);

        $matchAction->execute($result); //@TODO dispatch to process in the background

        return ResponseHelper::success($this->transformResponse($result));
    }

    private function transformResponse(ChallengeRequest $challengeRequest): object
    {
        return (object) [
            'challenge_request_id' => 'trivia-challenge-requests/' . $challengeRequest->challenge_request_id
        ];
    }
}

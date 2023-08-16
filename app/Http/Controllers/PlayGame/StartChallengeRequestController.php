<?php

namespace App\Http\Controllers\PlayGame;

use Illuminate\Http\Request;
use App\Models\ChallengeRequest;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StartChallengeRequest;
use App\Http\ResponseHelpers\ResponseHelper;
use App\Jobs\FillUpCashdropPools;
use Illuminate\Support\Facades\Log;
use App\Jobs\MatchChallengeRequest;
use App\Jobs\MatchWithHumanChallengeRequest;
use App\Services\PlayGame\StakingChallengeGameService;

class StartChallengeRequestController extends Controller
{
    public function __invoke(
        Request $request,
        StakingChallengeGameService $triviaChallengeService,
        StartChallengeRequest $requestModel
    ): JsonResponse {
        
        $data = $requestModel->validated();

        Log::info('START_CHALLENGE_REQUEST_PROCESS', [
            'user' => $request->user()->username,
            'validatedRequest' => $data,
        ]);

        $result = $triviaChallengeService->create($request->user(), $data);

        $matchedRequest = MatchWithHumanChallengeRequest::dispatchSync($result, $request->header('x-request-env'));
        if (!$matchedRequest) {
            MatchChallengeRequest::dispatch($result, $request->header('x-request-env'));
        }
        FillUpCashdropPools::dispatch($request->amount, $request->user());
        return ResponseHelper::success($this->transformResponse($result));
    }

    private function transformResponse(ChallengeRequest $challengeRequest): object
    {
        return (object) [
            'challenge_request_id' => 'trivia-challenge-requests/' . $challengeRequest->challenge_request_id
        ];
    }
}

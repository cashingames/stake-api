<?php

namespace App\Http\Controllers\PlayGame;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\ResponseHelpers\ResponseHelper;
use App\Jobs\FillCashdropPools;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use App\Services\PlayGame\StakingChallengeGameService;
use Illuminate\Support\Facades\Log;

class EndChallengeGameController extends Controller
{
    public function __invoke(
        Request $request,
        StakingChallengeGameService $triviaChallengeService,
        TriviaChallengeStakingRepository $triviaChallengeStakingRepository,
    ): JsonResponse {

        $data = $request->validate([
            'challenge_request_id' => ['required'],
            'selected_options' => ['nullable'],
            'consumed_boosts' => ['nullable']
        ]);

        Log::info('EndChallengeGameController', $data);
        $result = $triviaChallengeService->submit($data);
        if (!$result) {
            Log::error('CHALLENGE_SUBMIT_ERROR');
            return ResponseHelper::error('Unable to submit challenge');
        }
       
        $challengeRequest = $triviaChallengeStakingRepository->getRequestById($data['challenge_request_id']);
        FillCashdropPools::dispatch($challengeRequest->amount, $request->user());

        return ResponseHelper::success((object) ['score' => $result->score]);
    }
}

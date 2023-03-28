<?php

namespace App\Http\Controllers\PlayGame;

use App\Http\Controllers\Controller;
use App\Http\ResponseHelpers\ResponseHelper;
use App\Services\PlayGame\StakingChallengeGameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StartChallengeRequestController extends Controller
{

    public function __invoke(Request $request, StakingChallengeGameService $triviaChallengeService): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'category' => ['required', 'numeric', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'max:' . $user->wallet->non_withdrawable_balance],
        ]);

        $result = $triviaChallengeService->create($user, $data);

        return ResponseHelper::success($result);
    }
}

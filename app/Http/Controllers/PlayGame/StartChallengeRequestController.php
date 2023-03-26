<?php

namespace App\Http\Controllers\PlayGame;

use App\Http\Controllers\Controller;
use App\Http\ResponseHelpers\ResponseHelper;
use App\Services\PlayGame\TriviaChallengeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StartChallengeRequestController extends Controller
{

    public function __invoke(Request $request, TriviaChallengeService $triviaChallengeService): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'category' => ['required', 'numeric', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'max:' . $user->wallet->non_withdrawable_balance],
        ]);

        $requestId = $triviaChallengeService->create($user, $data);


        return ResponseHelper::success($requestId);
    }
}

<?php

namespace App\Http\Controllers\PlayGame;

use App\Enums\GameType;
use App\Http\Requests\StartSinglePlayerRequest;
use App\Http\ResponseHelpers\ResponseHelper;
use App\Services\PlayGame\PlayGameServiceFactory;
use Illuminate\Http\Request;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use stdClass;

class StartSinglePlayerGameController extends Controller
{

    public function __invoke(
        Request $request,
        StartSinglePlayerRequest $reqeuestModel,
        GameType $customType,
        PlayGameServiceFactory $gameService
    )
    {
        $validated = $reqeuestModel->validated();
        $validatedRequest = (object) $validated;

        Log::info('START_SINGLE_PLAYER_PROCESS', [
            'user' => $request->user()->username,
            'validatedRequest' => $validatedRequest,
            'gameType' => $customType
        ]);

        $startResponse = $gameService->startGame($validatedRequest);

        //@TODO: Handle business error states in the services
        if (count($startResponse->questions) < 10) {
            Log::info('SSTART_SINGLE_PLAYER_CANNOT_START', [
                'user' => $request->user()->username,
            ]);
            return ResponseHelper::error('Category not available for now, try again later', 400);
        }

        $result = $this->prepare($startResponse->gameSession, $startResponse->questions);
        return ResponseHelper::success($result);
    }


    private function prepare($gameSession, $questions): array
    {
        $gameInfo = new stdClass;
        $gameInfo->token = $gameSession->session_token;
        $gameInfo->startTime = $gameSession->start_time;
        $gameInfo->endTime = $gameSession->end_time;

        return [
            'questions' => $questions,
            'game' => $gameInfo
        ];
    }

}

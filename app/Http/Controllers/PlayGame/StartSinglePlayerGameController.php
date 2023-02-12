<?php

namespace App\Http\Controllers\PlayGame;

use App\Http\Requests\StartSinglePlayerRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Enums\GameType as EnumsGameType;
use App\Services\PlayGame\PlayGameServiceFactory;

use stdClass;

class StartSinglePlayerGameController extends BaseController
{

    public function __invoke(
        Request $request,
        StartSinglePlayerRequest $reqeuestModel,
        EnumsGameType $customType,
        PlayGameServiceFactory $gameService
    )
    {
        $validated = $reqeuestModel->validated();
        $validatedRequest = (object) $validated;

        $startResponse = $gameService->startGame($validatedRequest);
        $result = $this->formatResponse($startResponse->gameSession, $startResponse->questions);
        return $this->sendResponse($result, 'Game Started');

    }

    private function formatResponse($gameSession, $questions): array
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
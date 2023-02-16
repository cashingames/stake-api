<?php

namespace App\Http\Controllers\PlayGame;

use App\Http\Requests\StartSinglePlayerRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Enums\GameType;
use App\Services\PlayGame\PlayGameServiceFactory;

use stdClass;

class StartSinglePlayerGameController extends BaseController
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

        $startResponse = $gameService->startGame($validatedRequest);

        //@TODO: Handle business error states in the services
        if (count($startResponse->questions) < 10 && $customType != GameType::LiveTrivia) {
            return $this->sendError(
                'Category not available for now, try again later',
                'Category not available for now, try again later'
            );
        }

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

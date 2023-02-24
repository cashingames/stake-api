<?php

namespace App\Http\Controllers\PlayGame;

use App\Http\Controllers\BaseController;
use App\Models\GameSession;
use App\Enums\GameType;
use App\Http\Requests\StartSinglePlayerRequest;
use App\Services\PlayGame\PlayGameServiceFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        $gameService->giftReferrerOnFirstGame();

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

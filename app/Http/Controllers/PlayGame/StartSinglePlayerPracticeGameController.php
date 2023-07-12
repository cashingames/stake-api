<?php

namespace App\Http\Controllers\PlayGame;

use App\Enums\GameRequestMode;
use App\Http\Controllers\Controller;
use App\Http\ResponseHelpers\ResponseHelper;
use App\Models\ChallengeRequest;
use App\Repositories\Cashingames\TriviaQuestionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use stdClass;

class StartSinglePlayerPracticeGameController extends Controller
{
    public function __invoke(
        Request $request,
        TriviaQuestionRepository $questionRepository
    )
    {

        $data = $request->validate([
            'category' => ['required', 'numeric'],
            'amount' => ['required', 'numeric'],
        ]);

        $user = auth()->user();
        Log::info('START_PRACTICE_SINGLE_GAME', [
            'user' => $user->username,
            'validatedRequest' => $data,
        ]);

        $sessionToken = uniqid($user->id, true);

        ChallengeRequest::create([
            'challenge_request_id' => $sessionToken,
            'user_id' => $user->id,
            'username' => $user->username,
            'amount' => $data['amount'],
            'category_id' => $data['category'],
            'status' => 'ONGOING',
            'session_token' => $sessionToken,
            'request_mode' => GameRequestMode::SINGLE_PRACTICE->value
        ]);

        $questions = $questionRepository->getPracticeQuestionsWithCategoryId($data['category']);

        $result = $this->prepare($questions, $sessionToken);
        return ResponseHelper::success($result);
    }

    private function prepare($questions, $sessionToken): array
    {
        $gameInfo = new stdClass;
        $gameInfo->token = $sessionToken;
        $gameInfo->startTime = now();
        $gameInfo->endTime = now()->addMinutes(1);

        Log::info('PRACTICE_SINGLE_GAME_DATA', [
            'user' => auth()->user()->username,
            'gameData' => $gameInfo,
        ]);

        return [
            'questions' => $questions,
            'game' => $gameInfo
        ];
    }
}

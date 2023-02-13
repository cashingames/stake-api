<?php

namespace App\Services\PlayGame;

use App\Enums\FeatureFlags;
use App\Models\GameSession;
use App\Services\FeatureFlag;
use App\Services\OddsComputer;
use App\Services\QuestionsHardeningServiceInterface;
use App\Services\LiveTriviaQuestionsHardeningService;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LiveTriviaGameService implements PlayGameServiceInterface
{

    private \stdClass $validatedRequest;

    private User $user;

    private QuestionsHardeningServiceInterface $questionsHardeningService;


    public function __construct()
    {
        $this->questionsHardeningService = new LiveTriviaQuestionsHardeningService();
        $this->user = auth()->user();
    }


    public function startGame(\stdClass $validatedRequest): array
    {
        $this->validatedRequest = $validatedRequest;

        $questions = $this->questionsHardeningService
            ->determineQuestions($this->user->id, $this->validatedRequest->category, $this->validatedRequest->trivia);

        DB::beginTransaction();

        $odds = $this->getMultiplierOdds();
        $gameSession = $this->generateSession($odds);
        $this->logQuestions($questions, $gameSession);

        DB::commit();

        return [
            'gameSession' => $gameSession,
            'questions' => $questions
        ];

    }

    private function generateSession($odds): GameSession
    {
        $gameSession = new GameSession();
        $gameSession->user_id = auth()->user()->id;
        $gameSession->game_mode_id = $this->validatedRequest->mode;
        $gameSession->game_type_id = $this->validatedRequest->type;
        $gameSession->category_id = $this->validatedRequest->category;
        $gameSession->session_token = Str::random(40);
        $gameSession->start_time = Carbon::now();
        $gameSession->end_time = Carbon::now()->addMinutes(1);
        $gameSession->state = "ONGOING";
        $gameSession->trivia_id = $this->validatedRequest->trivia;

        $gameSession->odd_multiplier = $odds->oddsMultiplier;
        $gameSession->odd_condition = $odds->oddsCondition;

        $gameSession->save();

        return $gameSession;
    }

    private function getMultiplierOdds(): \stdClass
    {
        if (!FeatureFlag::isEnabled(FeatureFlags::ODDS)) {
            return (object) [
                'oddsMultiplier' => 1,
                'oddsCondition' => 'no_matching_condition'
            ];
        }

        $oddMultiplierComputer = new OddsComputer();

        $average = $this->getAverageOfLastThreeGames();
        return (object) $oddMultiplierComputer->compute($this->user, $average, false);
    }

    public function getAverageOfLastThreeGames(): float
    {
        return $this->user->gameSessions()
            ->whereNotNull("trivia_id")
            ->completed()
            ->latest()
            ->limit(3)
            ->avg('correct_count') ?? 0.0;
    }

    public function logQuestions($questions, $gameSession): void
    {
        $data = [];

        foreach ($questions as $question) {
            $data[] = [
                'question_id' => $question->id,
                'game_session_id' => $gameSession->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('game_session_questions')->insert($data);
    }
}
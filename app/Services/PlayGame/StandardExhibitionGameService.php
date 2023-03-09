<?php

namespace App\Services\PlayGame;

use App\Enums\FeatureFlags;
use App\Models\GameSession;
use App\Models\UserPlan;
use App\Services\FeatureFlag;
use App\Services\OddsComputer;
use App\Services\QuestionsHardeningServiceInterface;
use App\Services\StakeQuestionsHardeningService;
use App\Services\StandardExhibitionQuestionsHardeningService;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class StandardExhibitionGameService implements PlayGameServiceInterface
{

    private \stdClass $validatedRequest;

    private User $user;

    private QuestionsHardeningServiceInterface $questionsHardeningService;

    public function __construct()
    {
        $this->questionsHardeningService = new StandardExhibitionQuestionsHardeningService();
        $this->user = auth()->user();
    }

    public function startGame(\stdClass $validatedRequest): array
    {
        $this->validatedRequest = $validatedRequest;

        $questions = $this->questionsHardeningService
            ->determineQuestions($this->user->id, $this->validatedRequest->category, null);

        DB::beginTransaction();

        $odds = $this->getMultiplierOdds();
        $planId = $this->applyGamePlan();
        $gameSession = $this->generateSession($odds, $planId);
        $this->logQuestions($questions, $gameSession);

        DB::commit();

        return [
            'gameSession' => $gameSession,
            'questions' => $questions
        ];

    }

    private function generateSession($odds, $planId): GameSession
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

        $gameSession->odd_multiplier = $odds->oddsMultiplier;
        $gameSession->odd_condition = $odds->oddsCondition;

        $gameSession->plan_id = $planId;

        $gameSession->save();

        return $gameSession;
    }

    private function applyGamePlan(): int
    {
        $plan = $this->user->getNextFreePlan() ?? $this->user->getNextPaidPlan();
        $userPlan = UserPlan::where('id', $plan->pivot->id)->first();
        $userPlan->update(['used_count' => $userPlan->used_count + 1]);

        if ($plan->game_count * $userPlan->plan_count <= $userPlan->used_count) {
            $userPlan->update(['is_active' => false]);
        }

        return $plan->id;
    }

    private function getMultiplierOdds(): \stdClass
    {
        if (!FeatureFlag::isEnabled(FeatureFlags::ODDS)) {
            return (object) [
                'oddsMultiplier' => 1,
                'oddsCondition' => 'no_matching_condition'
            ];
        }
        $average = $this->getAverageOfLastThreeGames();

        $oddMultiplierComputer = new OddsComputer();
        return (object) $oddMultiplierComputer->compute($this->user, $average, false);
    }

    private function getAverageOfLastThreeGames(): float
    {
        return $this->user->gameSessions()
            ->completed()
            ->latest()
            ->limit(3)
            ->avg('correct_count') ?? 0.0;
    }

    private function logQuestions($questions, $gameSession): void
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

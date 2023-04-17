<?php

namespace App\Services\PlayGame;

use App\Enums\FeatureFlags;
use App\Enums\GameSessionStatus;
use App\Models\ExhibitionStaking;
use App\Models\GameSession;
use App\Models\Staking;
use App\Models\WalletTransaction;
use App\Services\FeatureFlag;
use App\Services\QuestionsHardeningServiceInterface;
use App\Services\StakeQuestionsHardeningService;
use App\Services\TriviaStaking\OddsService;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StakingExhibitionGameService implements PlayGameServiceInterface
{

    private \stdClass $validatedRequest;

    private User $user;

    public function __construct(
        private StakeQuestionsHardeningService $stakeQuestionsHardeningService,
    ) {
        $this->user = auth()->user();
    }

    public function startGame(\stdClass $validatedRequest): array
    {
        $this->validatedRequest = $validatedRequest;

        $questions = $this->stakeQuestionsHardeningService
            ->determineQuestions($this->user->id, $this->validatedRequest->category, null);

        if ($questions->count() > 10) {
            DB::beginTransaction();

            $gameSession = $this->generateSession();
            $stakingId = $this->stakeAmount($validatedRequest->staking_amount);
            $this->createExhibitionStaking($stakingId, $gameSession->id);
            $this->logQuestions($questions, $gameSession);

            DB::commit();

            return [
                'gameSession' => $gameSession,
                'questions' => $questions
            ];
        }
        return [
            'gameSession' => null,
            'questions' => []
        ];
    }

    private function generateSession(): GameSession
    {
        $gameSession = new GameSession();
        $gameSession->user_id = auth()->user()->id;
        $gameSession->game_mode_id = $this->validatedRequest->mode;
        $gameSession->game_type_id = $this->validatedRequest->type;
        $gameSession->category_id = $this->validatedRequest->category;
        $gameSession->session_token = Str::random(40);
        $gameSession->start_time = Carbon::now();
        $gameSession->end_time = Carbon::now()->addMinutes(1);
        $gameSession->state = GameSessionStatus::ONGOING;

        $gameSession->save();

        return $gameSession;
    }

    public function stakeAmount($stakingAmount)
    {
        $this->user->wallet->non_withdrawable_balance -= $stakingAmount;
        $this->user->wallet->save();

        WalletTransaction::create([
            'wallet_id' => $this->user->wallet->id,
            'transaction_type' => 'DEBIT',
            'amount' => $stakingAmount,
            'balance' => $this->user->wallet->non_withdrawable_balance,
            'description' => 'Placed a staking of ' . $stakingAmount,
            'reference' => Str::random(10),
        ]);

        $odd = 1;

        /**
         * @TODO Rename to dynamic staking odds
         */
        if (FeatureFlag::isEnabled(FeatureFlags::STAKING_WITH_ODDS)) {
            $oddMultiplierComputer = app(OddsService::class);
            $oddMultiplier = $oddMultiplierComputer->computeDynamicOdds($this->user);
            $odd = $oddMultiplier['oddsMultiplier'];
        }

        $staking = Staking::create([
            'amount_staked' => $stakingAmount,
            'odd_applied_during_staking' => $odd,
            'user_id' => $this->user->id //@TODO remove from exhibition staking, not in use
        ]);

        return $staking->id;
    }

    public function createExhibitionStaking($stakingId, $gameSessionId): void
    {
        ExhibitionStaking::create([
            'game_session_id' => $gameSessionId,
            'staking_id' => $stakingId
        ]);
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

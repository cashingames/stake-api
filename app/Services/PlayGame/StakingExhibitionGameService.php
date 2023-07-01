<?php

namespace App\Services\PlayGame;

use App\Models\Staking;
use App\Enums\FeatureFlags;
use App\Models\GameSession;
use Illuminate\Support\Str;
use App\Services\FeatureFlag;
use Illuminate\Support\Carbon;
use App\Enums\StakingFundSource;
use App\Enums\GameSessionStatus;
use App\Models\ExhibitionStaking;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\User;
use App\Services\TriviaStaking\OddsService;
use App\Actions\Wallet\DebitWalletForStaking;
use App\Services\StakeQuestionsHardeningService;

class StakingExhibitionGameService implements PlayGameServiceInterface
{

    private \stdClass $validatedRequest;

    private User $user;

    public function __construct(
        private StakeQuestionsHardeningService $stakeQuestionsHardeningService,
        private readonly DebitWalletForStaking $walletDebitAction,
        private readonly StakingFundSource $fundSource
    ) {
        $this->user = auth()->user();
    }

    public function startGame(\stdClass $validatedRequest): array
    {
        $this->validatedRequest = $validatedRequest;

        $questions = $this->stakeQuestionsHardeningService
            ->determineQuestions($this->user->id, $this->validatedRequest->category);

        if ($questions->count() < 10) {
            return [
                'gameSession' => null,
                'questions' => []
            ];
        }

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

    private function generateSession(): GameSession
    {
        $gameSession = new GameSession();
        $gameSession->user_id = auth()->user()->id;
        $gameSession->game_mode_id = 1;
        $gameSession->game_type_id = 2;
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
        $this->walletDebitAction->execute($this->user->wallet, $stakingAmount);

        $odd = 1;

        /**
         * @TODO Rename to dynamic staking odds
         */

        $oddMultiplierComputer = app(OddsService::class);
        $oddMultiplier = $oddMultiplierComputer->computeDynamicOdds($this->user);
        $odd = $oddMultiplier['oddsMultiplier'];

        $staking = Staking::create([
            'amount_staked' => $stakingAmount,
            'odd_applied_during_staking' => $odd,
            'user_id' => $this->user->id, //@TODO remove from exhibition staking, not in use
            'fund_source' => $this->fundSource,
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

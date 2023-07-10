<?php

namespace App\Services\PlayGame;

use App\Models\Staking;
use App\Models\GameSession;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Enums\WalletBalanceType;
use App\Enums\GameSessionStatus;
use App\Models\ExhibitionStaking;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\User;
use App\Services\TriviaStaking\OddsService;
use App\Actions\Wallet\DebitWalletForStaking;
use App\Services\StakeQuestionsHardeningService;

class StakingExhibitionGameService
{

    private \stdClass $validatedRequest;

    private User $user;

    public function __construct(
        private StakeQuestionsHardeningService $stakeQuestionsHardeningService,
        private readonly DebitWalletForStaking $walletDebitAction,
        private readonly WalletBalanceType $walletBalanceType
    ) {
        $this->user = auth()->user();
    }

    public function startGame(\stdClass $validatedRequest): array
    {
        $this->validatedRequest = $validatedRequest;

        $questions = $this->stakeQuestionsHardeningService
            ->determineQuestions($this->user->id, $this->validatedRequest->category);

        if ($questions->count() < 10) {
            Log::info(
                'User has less than 10 questions',
                [
                    'user' => $this->user->username,
                    'category' => $this->validatedRequest->category,
                    'questions' => $questions->count()
                ]
            );

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
        $this->walletDebitAction->execute($this->user->wallet, $stakingAmount, $this->walletBalanceType);

        $odd = 1;

        /**
         * @TODO Rename to dynamic staking odds
         */

        $oddMultiplierComputer = app(OddsService::class);
        $oddMultiplier = $oddMultiplierComputer->computeDynamicOdds($this->user);
        $odd = $oddMultiplier['oddsMultiplier'];

        /**
         * We are assuming that all bonus is registration bonus
         * What if we have cashback bonus ?
         * How do we different which bonus the user is playing with
         */
        $staking = Staking::create([
            'amount_staked' => $stakingAmount,
            'odd_applied_during_staking' => $odd,
            'user_id' => $this->user->id, //@TODO remove from exhibition staking, not in use
            'fund_source' => $this->walletBalanceType,
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

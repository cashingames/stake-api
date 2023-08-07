<?php

namespace App\Actions\TriviaChallenge;

use App\Actions\ActionHelpers\ChallengeRequestMatchHelper;
use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Enums\WalletTransactionType;
use App\Models\ChallengeRequest;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use App\Repositories\Cashingames\WalletRepository;
use App\Repositories\Cashingames\WalletTransactionDto;
use Illuminate\Support\Carbon;

class VerifyChallengeWinnerAction
{
    public function __construct(
        private readonly TriviaChallengeStakingRepository $triviaChallengeStakingRepository,
        private readonly WalletRepository $walletRepository,
        private readonly ChallengeRequestMatchHelper $challengeHelper
    ) {
    }

    public function execute(ChallengeRequest $request, ChallengeRequest $matchedRequest): void
    {
        if (
            Carbon::parse($request->ended_at)->diffInMinutes(now()) >= 1 &&
            $matchedRequest->ended_at == null
        ) {
            $this->creditWinner($request);
            $this->triviaChallengeStakingRepository->updateSystemCompletedRequest($matchedRequest->challenge_request_id);
            $this->challengeHelper->updateEndMatchFirestore($request, $matchedRequest);
        }
        return;
    }

    private function creditWinner(ChallengeRequest $winner): void
    {
        $amountWon = $winner->amount * 2;
        $this->walletRepository->addTransaction(
            new WalletTransactionDto(
                $winner->user_id,
                $amountWon,
                'Challenge game Winnings credited',
                WalletBalanceType::WinningsBalance,
                WalletTransactionType::Credit,
                WalletTransactionAction::WinningsCredited,

            )
        );

        $winner->amount_won = $amountWon;
        $winner->save();
    }
}

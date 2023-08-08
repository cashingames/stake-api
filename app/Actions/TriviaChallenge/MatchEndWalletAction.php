<?php

namespace App\Actions\TriviaChallenge;

use App\Actions\ActionHelpers\ChallengeRequestMatchHelper;
use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Enums\WalletTransactionType;
use App\Jobs\SendChallengeRefundNotification;
use App\Jobs\VerifyChallengeWinner;
use App\Models\ChallengeRequest;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use App\Repositories\Cashingames\WalletRepository;
use App\Repositories\Cashingames\WalletTransactionDto;
use Illuminate\Support\Facades\Log;

class MatchEndWalletAction
{
    public function __construct(
        private readonly TriviaChallengeStakingRepository $triviaChallengeStakingRepository,
        private readonly WalletRepository $walletRepository,
        private readonly ChallengeRequestMatchHelper $challengeHelper
    ) {
    }

    public function execute(string $requestId): ChallengeRequest|null
    {
        //@note: $winner comes before isComplete because the request and matchRequest objects are need in isComplete
        $winner = $this->getChallengeWinner(
            $request = $this->triviaChallengeStakingRepository->getRequestById($requestId),
            $matchedRequest = $this->triviaChallengeStakingRepository->getMatchedRequestById($requestId)
        );

        $isComplete = $this->challengeHelper->isBothCompleted($request, $matchedRequest);
        Log::info('isComplete: ' . $isComplete);
        if (!$isComplete) {
            //indicate we are waiting for opponent
            VerifyChallengeWinner::dispatch($request, $matchedRequest)->delay(now()->addMinute());
            return null;
        }

        if ($winner == null) {
            $this->refundMatchedOpponents($request, $matchedRequest);
        } else {
            $this->challengeHelper->creditWinner($request);
        }

        return $winner;
    }

    private function refundMatchedOpponents(ChallengeRequest $request, ChallengeRequest $matchedRequest): void
    {
        $description = 'Challenge game stake refund';

        $this->walletRepository->addTransaction(
            new WalletTransactionDto(
                $request->user_id,
                $request->amount,
                $description,
                WalletBalanceType::from($request->fund_source ?? WalletBalanceType::CreditsBalance->value),
                WalletTransactionType::Credit,
                WalletTransactionAction::FundsReversed
            )
        );

        SendChallengeRefundNotification::dispatch( $request , $request->user);

        $this->walletRepository->addTransaction(
            new WalletTransactionDto(
                $matchedRequest->user_id,
                $matchedRequest->amount,
                $description,
                WalletBalanceType::from($matchedRequest->fund_source ?? WalletBalanceType::CreditsBalance->value),
                WalletTransactionType::Credit,
                WalletTransactionAction::FundsReversed
            )
        );

        SendChallengeRefundNotification::dispatch( $matchedRequest, $matchedRequest->user);
        
    }

    private function getChallengeWinner(
        ChallengeRequest $request,
        ChallengeRequest $matchedRequest
    ): ChallengeRequest|null {

        if (!$this->challengeHelper->isBothCompleted($request, $matchedRequest)) {
            return null;
        }

        if ($request->score == $matchedRequest->score) {
            return null;
        }

        return $request->score > $matchedRequest->score ? $request : $matchedRequest;
    }
}

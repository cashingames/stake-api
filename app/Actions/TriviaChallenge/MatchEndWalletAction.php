<?php

namespace App\Actions\TriviaChallenge;

use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Enums\WalletTransactionType;
use App\Jobs\SendChallengeRefundNotification;
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
    ) {
    }

    public function execute(string $requestId): ChallengeRequest|null
    {
        //@note: $winner comes before isComplete because the request and matchRequest objects are need in isComplete
        $winner = $this->getChallengeWinner(
            $request = $this->triviaChallengeStakingRepository->getRequestById($requestId),
            $matchedRequest = $this->triviaChallengeStakingRepository->getMatchedRequestById($requestId)
        );

        $isComplete = $this->isCompleted($request, $matchedRequest);
        Log::info('isComplete: ' . $isComplete);
        if (!$isComplete) {
            return null;
        }

        if ($winner == null) {
            $this->refundMatchedOpponents($request, $matchedRequest);
        } else {
            $this->creditWinner($winner);
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

        ChallengeRequest::where('challenge_request_id', $winner->challenge_request_id)
            ->update([
                'amount_won' => $amountWon
            ]);
    }

    private function getChallengeWinner(
        ChallengeRequest $request,
        ChallengeRequest $matchedRequest
    ): ChallengeRequest|null {

        if (!$this->isCompleted($request, $matchedRequest)) {
            return null;
        }

        if ($request->score == $matchedRequest->score) {
            return null;
        }

        return $request->score > $matchedRequest->score ? $request : $matchedRequest;
    }

    private function isCompleted(
        ChallengeRequest $request,
        ChallengeRequest $matchedRequest
    ): bool {
        return $matchedRequest->status == 'COMPLETED' && $request->status == 'COMPLETED';
    }


}

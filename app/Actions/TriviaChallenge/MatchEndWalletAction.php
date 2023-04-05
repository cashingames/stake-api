<?php

namespace App\Actions\TriviaChallenge;

use App\Actions\Wallet\CreditWalletAction;
use App\Models\ChallengeRequest;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use Illuminate\Support\Facades\Log;

class MatchEndWalletAction
{
    public function __construct(
        private readonly TriviaChallengeStakingRepository $triviaChallengeStakingRepository,
        private readonly CreditWalletAction $creditWalletAction,
    ) {
    }

    public function execute(string $requestId): ChallengeRequest|null
    {
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
        $this->creditWalletAction->executeRefund(
            $request->user->wallet,
            $request->amount,
            'Trivia challenge staking refund'
        );

        $this->creditWalletAction->executeRefund(
            $matchedRequest->user->wallet,
            $matchedRequest->amount,
            'Trivia challenge staking refund'
        );

    }

    private function creditWinner(ChallengeRequest $winner): void
    {
        $amountWon = $winner->amount * 2;
        $this->creditWalletAction->execute(
            $winner->user->wallet,
            $amountWon,
            'Trivia challenge staking winning'
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

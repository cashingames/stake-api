<?php

namespace App\Actions\TriviaChallenge;

use App\Actions\Wallet\CreditWalletAction;
use App\Models\ChallengeRequest;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;

class MatchEndWalletAction
{
    public function __construct(
        private readonly TriviaChallengeStakingRepository $triviaChallengeStakingRepository,
        private readonly CreditWalletAction $creditWalletAction,
    ) {
    }

    public function execute(string $requestId): ChallengeRequest|null|int
    {
        $winner = $this->getChallengeWinner(
            $request = $this->triviaChallengeStakingRepository->getRequestById($requestId),
            $matchedRequest = $this->triviaChallengeStakingRepository->getMatchedRequestById($requestId)
        );

        if ($winner == -1) {
            $this->creditWalletAction->execute(
                $request->user->wallet,
                $request->amount,
                'Trivia challenge staking refund'
            );

            $this->creditWalletAction->execute(
                $matchedRequest->user->wallet,
                $matchedRequest->amount,
                'Trivia challenge staking refund'
            );

        } elseif ($winner !== null) {
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


        return $winner;
    }

    private function getChallengeWinner(
        ChallengeRequest $request,
        ChallengeRequest $matchedRequest
    ): ChallengeRequest|null|int {

        if ($matchedRequest->status !== 'COMPLETED' || $request->status !== 'COMPLETED') {
            return null;
        }

        if ($request->score === $matchedRequest->score) {
            return -1;
        }

        return $request->score > $matchedRequest->score ? $request : $matchedRequest;
    }


}
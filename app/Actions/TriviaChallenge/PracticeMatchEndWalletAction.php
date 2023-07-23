<?php

namespace App\Actions\TriviaChallenge;
use App\Models\ChallengeRequest;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use App\Repositories\Cashingames\WalletRepository;

class PracticeMatchEndWalletAction
{
    public function __construct(
        private readonly TriviaChallengeStakingRepository $triviaChallengeStakingRepository,
        private readonly WalletRepository $walletRepository,
    ) {
    }

    public function execute(string $requestId): ChallengeRequest|null
    {

        $winner = $this->getChallengeWinner(
            $this->triviaChallengeStakingRepository->getRequestById($requestId),
            $this->triviaChallengeStakingRepository->getMatchedRequestById($requestId)
        );

        if ($winner != null) {
            $this->creditWinner($winner);
        }

        return $winner;
    }


    private function getChallengeWinner(
        ChallengeRequest $request, ChallengeRequest $matchedRequest
    ): ChallengeRequest|null {
        $winner = null;
        if ($request->score > $matchedRequest->score) {
            $winner = $request;
        } elseif ($request->score < $matchedRequest->score) {
            $winner = $matchedRequest;
        }
        return $winner;
    }

    private function creditWinner(ChallengeRequest $winner): void
    {
        $amountWon = $winner->amount * 2;
        ChallengeRequest::where('challenge_request_id', $winner->challenge_request_id)
            ->update([
                'amount_won' => $amountWon
            ]);
    }

}
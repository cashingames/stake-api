<?php

namespace App\Actions\TriviaChallenge;

use App\Actions\ActionHelpers\ChallengeRequestMatchHelper;
use App\Models\ChallengeRequest;
use App\Repositories\Cashingames\WalletRepository;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;

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
        if ($this->challengeHelper->isCompleted($matchedRequest)) {
            return;
        }

        $this->challengeHelper->creditWinner($request);
        $this->triviaChallengeStakingRepository
            ->updateSystemCompletedRequest($matchedRequest->challenge_request_id);

        $this->challengeHelper
            ->updateEndMatchFirestore($request, $matchedRequest);

    }
}
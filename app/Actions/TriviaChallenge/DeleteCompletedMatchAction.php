<?php

namespace App\Actions\TriviaChallenge;

use App\Models\ChallengeRequest;
use App\Services\Firebase\FirestoreService;

class DeleteCompletedMatchAction
{
    public function __construct(
        private readonly FirestoreService $firestoreService,
    ) {
    }

    public function execute(ChallengeRequest $challengeRequest, ChallengeRequest $opponentRequest): void
    {
        $this->firestoreService->deleteDocument(
            'trivia-challenge-requests',
            $challengeRequest->challenge_request_id
        );

        $this->firestoreService->deleteDocument(
            'trivia-challenge-requests',
            $opponentRequest->challenge_request_id
        );

    }

}

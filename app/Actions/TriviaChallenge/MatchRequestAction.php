<?php

namespace App\Actions\TriviaChallenge;

use App\Models\ChallengeRequest;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use App\Repositories\Cashingames\TriviaQuestionRepository;
use App\Services\Firebase\FirestoreService;

class MatchRequestAction
{
    public function __construct(
        private readonly TriviaChallengeStakingRepository $triviaChallengeStakingRepository,
        private readonly FirestoreService $firestoreService,
        private readonly TriviaQuestionRepository $triviaQuestionRepository,
    ) {
    }

    public function execute(ChallengeRequest $challengeRequest): ChallengeRequest|null
    {
        $matchedRequest = $this->triviaChallengeStakingRepository->findMatch($challengeRequest);
        if (!$matchedRequest) {
            echo 'no match';
            return null;
        }

        $this->updateFirestore($challengeRequest, $matchedRequest);

        $this->removeFromMatching($challengeRequest, $matchedRequest);

        echo 'done';
        return $matchedRequest;
    }


    private function updateFirestore(ChallengeRequest $challengeRequest, ChallengeRequest $matchedRequest): void
    {
        $questions = $this
            ->triviaQuestionRepository
            ->getRandomEasyQuestionsWithCategoryId($challengeRequest->category_id)
            ->toArray();

        $this->firestoreService->updateDocument(
            'trivia-challenge-requests',
            $challengeRequest->challenge_request_id,
            [
                'status' => 'MATCHED',
                'questions' => $questions,
                'opponent' => $matchedRequest->toArray(),
            ]
        );

        $this->firestoreService->updateDocument(
            'trivia-challenge-requests',
            $matchedRequest->challenge_request_id,
            [
                'status' => 'MATCHED',
                'questions' => $questions,
                'opponent' => $challengeRequest->toArray(),
            ]
        );
    }

    private function removeFromMatching(ChallengeRequest $challengeRequest, ChallengeRequest $matchedRequest): void
    {
        $this->triviaChallengeStakingRepository->removeFromMatching($challengeRequest);
        $this->triviaChallengeStakingRepository->removeFromMatching($matchedRequest);
    }

}

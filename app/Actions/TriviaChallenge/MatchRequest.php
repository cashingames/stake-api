<?php

namespace App\Actions\TriviaChallenge;

use App\Models\ChallengeRequest;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use App\Repositories\Cashingames\TriviaQuestionRepository;
use App\Services\Firebase\FirestoreService;

class MatchRequest
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
            return null;
        }

        $this->updateFirestore($challengeRequest, $matchedRequest);

        $this->removeFromMatching($challengeRequest, $matchedRequest);

        return $matchedRequest;
    }


    private function updateFirestore(ChallengeRequest $challengeRequest, ChallengeRequest $matchedRequest): void
    {
        $questions = $this
            ->triviaQuestionRepository
            ->getRandomEasyQuestionsWithCategoryId($challengeRequest->category_id);

        $this->firestoreService->setDocument(
            'trivia-challenge-requests',
            $challengeRequest->challenge_request_id,
            [
                'status' => 'MATCHED',
                'questions' => $questions,
                'opponent' => $matchedRequest,
            ]
        );

        $this->firestoreService->setDocument(
            'trivia-challenge-requests',
            $matchedRequest->challenge_request_id,
            [
                'status' => 'MATCHED',
                'questions' => $questions,
                'opponent' => $challengeRequest,
            ]
        );
    }

    private function removeFromMatching(ChallengeRequest $challengeRequest, ChallengeRequest $matchedRequest): void
    {
        $this->triviaChallengeStakingRepository->removeFromMatching($challengeRequest);
        $this->triviaChallengeStakingRepository->removeFromMatching($matchedRequest);
    }

}
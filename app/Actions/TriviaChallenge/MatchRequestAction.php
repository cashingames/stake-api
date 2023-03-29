<?php

namespace App\Actions\TriviaChallenge;

use App\Models\ChallengeRequest;
use App\Services\Firebase\FirestoreService;
use App\Repositories\Cashingames\TriviaQuestionRepository;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;

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
            return null;
        }

        $this->triviaChallengeStakingRepository->updateAsMatched($challengeRequest, $matchedRequest);

        $questions = $this->processQuestions($challengeRequest, $matchedRequest);

        $this->updateFirestore($challengeRequest, $matchedRequest, $questions);

        return $matchedRequest;
    }

    private function processQuestions(ChallengeRequest $challengeRequest, ChallengeRequest $matchedRequest): array
    {
        $questions = $this
            ->triviaQuestionRepository
            ->getRandomEasyQuestionsWithCategoryId($challengeRequest->category_id)
            ->toArray();

        $this->triviaChallengeStakingRepository->logQuestions($questions, $challengeRequest, $matchedRequest);

        return $questions;
    }


    private function updateFirestore(
        ChallengeRequest $challengeRequest,
        ChallengeRequest $matchedRequest,
        array $questions
    ): void {

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
}

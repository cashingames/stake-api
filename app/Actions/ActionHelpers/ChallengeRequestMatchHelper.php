<?php

namespace App\Actions\ActionHelpers;

use Illuminate\Support\Collection;
use App\Models\ChallengeRequest;
use App\Services\Firebase\FirestoreService;
use App\Repositories\Cashingames\TriviaQuestionRepository;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use App\Services\PlayGame\StakingChallengeGameService;

class ChallengeRequestMatchHelper
{
    private static FirestoreService $firestoreService;

    public function __construct(
        private readonly TriviaChallengeStakingRepository $triviaChallengeStakingRepository,
        private readonly TriviaQuestionRepository $triviaQuestionRepository,
        private readonly StakingChallengeGameService $triviaChallengeService,
    ) {
    }

    public function setFirestoreService($firestoreService) {
        self::$firestoreService = $firestoreService;
    }
    
    public function processQuestions(ChallengeRequest $challengeRequest, ChallengeRequest $matchedRequest): Collection
    {
        $questions = $this
            ->triviaQuestionRepository
            ->getRandomEasyQuestionsWithCategoryId($challengeRequest->category_id);

        $this->triviaChallengeStakingRepository->logQuestions(
            $questions->toArray(),
            $challengeRequest,
            $matchedRequest
        );

        return $questions;
    }

    public function updateFirestore(
        ChallengeRequest $challengeRequest,
        ChallengeRequest $matchedRequest,
        Collection $questions
    ): void {

        $this->firestoreService->updateDocument(
            'trivia-challenge-requests',
            $challengeRequest->challenge_request_id,
            [
                'status' => 'MATCHED',
                'questions' => $this->parseQuestions($questions),
                'opponent' => $this->parseOpponent($matchedRequest),
            ]
        );
        $this->firestoreService->updateDocument(
            'trivia-challenge-requests',
            $matchedRequest->challenge_request_id,
            [
                'status' => 'MATCHED',
                'questions' => $this->parseQuestions($questions),
                'opponent' => $this->parseOpponent($challengeRequest),
            ]
        );
    }

    public function parseQuestions(Collection $questions): array
    {
        return $questions->map(fn ($question) => [
            'id' => $question->id,
            'label' => $question->label,
            'options' => $question->options->map(fn ($option) => [
                'id' => $option->id,
                'title' => $option->title,
                'question_id' => $option->question_id,
            ])->toArray(),
        ])->toArray();
    }

    public function parseOpponent(ChallengeRequest $challengeRequest): array
    {
        return [
            'challenge_request_id' => $challengeRequest->challenge_request_id,
            'username' => $challengeRequest->username,
            'avatar' => $challengeRequest->user->profile->avatar,
            'status' => $challengeRequest->status,
        ];
    }
}
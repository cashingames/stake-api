<?php

namespace App\Actions\TriviaChallenge;

use App\Models\ChallengeRequest;
use App\Models\User;
use App\Services\Firebase\FirestoreService;
use App\Repositories\Cashingames\TriviaQuestionRepository;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use App\Services\PlayGame\StakingChallengeGameService;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

class MatchRequestAction
{
    public function __construct(
        private readonly TriviaChallengeStakingRepository $triviaChallengeStakingRepository,
        private readonly FirestoreService $firestoreService,
        private readonly TriviaQuestionRepository $triviaQuestionRepository,
        private readonly  StakingChallengeGameService $triviaChallengeService,
    ) {
    }

    public function execute(ChallengeRequest $challengeRequest): ChallengeRequest|null
    {

        $matchedRequest = $this->triviaChallengeStakingRepository->findMatch($challengeRequest);

        if (!$matchedRequest) {
            $matchedRequest = $this->matchWithBot($challengeRequest, $this->triviaChallengeService);
        }

        $this->triviaChallengeStakingRepository->updateAsMatched($challengeRequest, $matchedRequest);

        $questions = $this->processQuestions($challengeRequest, $matchedRequest);

        $this->updateFirestore($challengeRequest->refresh(), $matchedRequest->refresh(), $questions);

        return $matchedRequest;
    }

    private function matchWithBot(ChallengeRequest $challengeRequest,StakingChallengeGameService $triviaChallengeService ): ChallengeRequest|null
    {
        $bot = User::find(1);
        $bot->username = FakerFactory::create()->userName();
       
        $bot->wallet->non_withdrawable_balance += $challengeRequest->amount;
        $bot->wallet->save();

        return $triviaChallengeService->create($bot, ['category' => $challengeRequest->category_id, 'amount' => $challengeRequest->amount]);
    }


    private function processQuestions(ChallengeRequest $challengeRequest, ChallengeRequest $matchedRequest): Collection
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


    private function updateFirestore(
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

    private function parseQuestions(Collection $questions): array
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

    private function parseOpponent(ChallengeRequest $challengeRequest): array
    {
        return [
            'challenge_request_id' => $challengeRequest->challenge_request_id,
            'username' => $challengeRequest->username,
            'avatar' => $challengeRequest->user->profile->avatar,
            'status' => $challengeRequest->status,
        ];
    }
}

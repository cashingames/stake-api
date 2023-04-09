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
use Illuminate\Support\Lottery;

class MatchRequestAction
{
    private FirestoreService $firestoreService;

    public function __construct(
        private readonly TriviaChallengeStakingRepository $triviaChallengeStakingRepository,
        private readonly TriviaQuestionRepository $triviaQuestionRepository,
        private readonly StakingChallengeGameService $triviaChallengeService,
    ) {
    }

    public function execute(ChallengeRequest $challengeRequest, string $env): ChallengeRequest|null
    {
        $this->firestoreService = new FirestoreService($env);

        if ($challengeRequest->status !== 'MATCHING') {
            return null;
        }

        $matchedRequest = $this->triviaChallengeStakingRepository->findMatch($challengeRequest);

        if (!$matchedRequest) {
            $matchedRequest = $this->matchWithBot($challengeRequest, $env);
        }

        $this->triviaChallengeStakingRepository->updateAsMatched($challengeRequest, $matchedRequest);

        $questions = $this->processQuestions($challengeRequest, $matchedRequest);

        $this->updateFirestore($challengeRequest->refresh(), $matchedRequest->refresh(), $questions);

        return $matchedRequest;
    }

    private function matchWithBot(ChallengeRequest $challengeRequest, string $env): ChallengeRequest|null
    {
        $bot = User::find(1);

        $faker = FakerFactory::create('en_NG');

        Lottery::odds(1, 2)
            ->winner(function () use (&$bot, $faker) {
                $bot->username = strtolower($faker->userName());
            })
            ->loser(function () use (&$bot, $faker) {
                $bot->username = strtolower($faker->firstName());
            })
            ->choose();

        return $this->triviaChallengeService->create(
            $bot,
            [
                'category' => $challengeRequest->category_id,
                'amount' => $challengeRequest->amount,
                'env' => $env,
            ]
        );
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
        return $questions->map(fn($question) => [
            'id' => $question->id,
            'label' => $question->label,
            'options' => $question->options->map(fn($option) => [
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

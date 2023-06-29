<?php

namespace App\Actions\TriviaChallenge;

use App\Actions\ActionHelpers\ChallengeRequestMatchHelper;
use App\Models\ChallengeRequest;
use App\Models\User;
use App\Services\Firebase\FirestoreService;
use App\Repositories\Cashingames\TriviaQuestionRepository;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use App\Services\PlayGame\StakingChallengeGameService;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Lottery;

class MatchRequestAction
{

    public function __construct(
        private readonly TriviaChallengeStakingRepository $triviaChallengeStakingRepository,
        private readonly TriviaQuestionRepository $triviaQuestionRepository,
        private readonly StakingChallengeGameService $triviaChallengeService,
        private readonly ChallengeRequestMatchHelper $challengeRequestMatchHelper,
    ) {
    }

    public function execute(ChallengeRequest $challengeRequest, string $env): ChallengeRequest|null
    {
        if ($challengeRequest->status !== 'MATCHING') {
            return null;
        }

        $this->challengeRequestMatchHelper->setFirestoreService(app(FirestoreService::class, ['env' => $env]));
        $matchedRequest = $this->triviaChallengeStakingRepository->findMatch($challengeRequest);

        if (!$matchedRequest) {
            $matchedRequest = $this->matchWithBot($challengeRequest);
        }

        $this->triviaChallengeStakingRepository->updateAsMatched($challengeRequest, $matchedRequest);

        $questions = $this->challengeRequestMatchHelper->processQuestions($challengeRequest, $matchedRequest);

        $this->challengeRequestMatchHelper
            ->updateFirestore($challengeRequest->refresh(), $matchedRequest->refresh(), $questions);

        return $matchedRequest;
    }

    private function matchWithBot(ChallengeRequest $challengeRequest): ChallengeRequest|null
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
            ]
        );
    }

}

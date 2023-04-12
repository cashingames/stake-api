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

class MatchWithBotRequestAction
{
    private ChallengeRequestMatchHelper $matchHelper;

    public function __construct(
        private readonly TriviaChallengeStakingRepository $triviaChallengeStakingRepository,
        private readonly TriviaQuestionRepository $triviaQuestionRepository,
        private readonly StakingChallengeGameService $triviaChallengeService,
    ) {
    }

    public function execute(ChallengeRequest $challengeRequest, string $env): ChallengeRequest|null
    {
        $this->matchHelper->setFirestoreService(app(FirestoreService::class, ['env' => $env])) ;

        $matchedRequest = $this->matchWithBot($challengeRequest);
        $this->triviaChallengeStakingRepository->updateAsMatched($challengeRequest, $matchedRequest);

        $questions = $this->matchHelper->processQuestions($challengeRequest, $matchedRequest);

        $this->matchHelper->updateFirestore($challengeRequest->refresh(), $matchedRequest->refresh(), $questions);

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

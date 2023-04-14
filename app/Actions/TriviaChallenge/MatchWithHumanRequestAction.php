<?php

namespace App\Actions\TriviaChallenge;

use App\Actions\ActionHelpers\ChallengeRequestMatchHelper;
use App\Models\ChallengeRequest;
use App\Services\Firebase\FirestoreService;
use App\Repositories\Cashingames\TriviaQuestionRepository;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use App\Services\PlayGame\StakingChallengeGameService;

class MatchWithHumanRequestAction
{
    private ChallengeRequestMatchHelper $matchHelper;

    public function __construct(
        private readonly TriviaChallengeStakingRepository $triviaChallengeStakingRepository,
        private readonly TriviaQuestionRepository $triviaQuestionRepository,
        private readonly StakingChallengeGameService $triviaChallengeService,
    ) {
        $this->matchHelper = new ChallengeRequestMatchHelper(
            $this->triviaChallengeStakingRepository,
            $this->triviaQuestionRepository,
            $this->triviaChallengeService
        );
    }

    public function execute(ChallengeRequest $challengeRequest, string $env): ChallengeRequest|null
    {
        $this->matchHelper->setFirestoreService(app(FirestoreService::class, ['env' => $env]));

        $matchedRequest = $this->triviaChallengeStakingRepository->findMatch($challengeRequest);

        if (!$matchedRequest) {
            return null;
        }

        $this->triviaChallengeStakingRepository->updateAsMatched($challengeRequest, $matchedRequest);

        $questions = $this->matchHelper->processQuestions($challengeRequest, $matchedRequest);

        $this->matchHelper->updateFirestore($challengeRequest->refresh(), $matchedRequest->refresh(), $questions);

        return $matchedRequest;
    }
}

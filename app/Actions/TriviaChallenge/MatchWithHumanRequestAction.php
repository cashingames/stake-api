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
    // private ChallengeRequestMatchHelper $matchHelper;

    public function __construct(
        private readonly TriviaChallengeStakingRepository $triviaChallengeStakingRepository,
        private readonly TriviaQuestionRepository $triviaQuestionRepository,
        private readonly StakingChallengeGameService $triviaChallengeService,
        private readonly ChallengeRequestMatchHelper $challengeRequestMatchHelper,
    ) {
    }

    public function execute(ChallengeRequest $challengeRequest): ChallengeRequest|null
    {
        // $this->challengeRequest->setFirestoreService(app(FirestoreService::class, ['env' => $env]));

        $matchedRequest = $this->triviaChallengeStakingRepository->findMatch($challengeRequest);

        if (!$matchedRequest) {
            return null;
        }

        $this->triviaChallengeStakingRepository->updateAsMatched($challengeRequest, $matchedRequest);

        $questions = $this->challengeRequestMatchHelper->processQuestions($challengeRequest, $matchedRequest);

        $this->challengeRequestMatchHelper->updateFirestore($challengeRequest->refresh(), $matchedRequest->refresh(), $questions);

        return $matchedRequest;
    }
}

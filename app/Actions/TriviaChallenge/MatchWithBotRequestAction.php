<?php

namespace App\Actions\TriviaChallenge;

use App\Actions\ActionHelpers\ChallengeRequestMatchHelper;
use App\Models\ChallengeRequest;
use App\Models\User;
use App\Repositories\Cashingames\TriviaQuestionRepository;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use App\Services\PlayGame\StakingChallengeGameService;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Lottery;

class MatchWithBotRequestAction
{

    public function __construct(
        private readonly TriviaChallengeStakingRepository $triviaChallengeStakingRepository,
        private readonly TriviaQuestionRepository $triviaQuestionRepository,
        private readonly StakingChallengeGameService $triviaChallengeService,
        private readonly ChallengeRequestMatchHelper $challengeRequestMatchHelper,
    ) {
      
    }

    public function execute(ChallengeRequest $challengeRequest): ChallengeRequest|null
    {
        Log::info('MatchWithBotRequestAction Executing', [
            'google_env' => 'in MatchWithHumanRequestAction ' . env('GOOGLE_APPLICATION_CREDENTIALS'),
            'challengeRequest' => $challengeRequest,
        ]);

        $matchedRequest = $this->matchWithBot($challengeRequest);
       
        $this->triviaChallengeStakingRepository->updateAsMatched($challengeRequest, $matchedRequest);

        $questions = $this->challengeRequestMatchHelper->processPracticeQuestions($challengeRequest, $matchedRequest);

        $this->challengeRequestMatchHelper->updateFirestore(
            $challengeRequest->refresh(), $matchedRequest->refresh(),
            $questions
        );

        return $matchedRequest;
    }

    private function matchWithBot(ChallengeRequest $challengeRequest): ChallengeRequest|null
    {
        $bot = User::find(1);

        $bot->username = 'Practice Bot';

        return $this->triviaChallengeService->createPracticeRequest(
            $bot,
            [
                'category' => $challengeRequest->category_id,
                'amount' => $challengeRequest->amount,
            ]
        );

    }







}
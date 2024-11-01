<?php

namespace App\Actions\TriviaChallenge;

use App\Models\User;
use App\Models\ChallengeRequest;
use Illuminate\Support\Facades\Log;
use App\Services\PlayGame\StakingChallengeGameService;
use App\Actions\ActionHelpers\ChallengeRequestMatchHelper;
use App\Repositories\Cashingames\TriviaQuestionRepository;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;

class MatchWithPracticeBotRequestAction
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
        Log::info('MatchWithPracticeBotRequestAction Executing', [
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

        $bot->username = 'Ife';

        return $this->triviaChallengeService->createPracticeRequest(
            $bot,
            [
                'category' => $challengeRequest->category_id,
                'amount' => $challengeRequest->amount,
            ]
        );

    }







}
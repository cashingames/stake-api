<?php

namespace Tests\Unit\ActionHelpers;

use App\Actions\TriviaChallenge\MatchWithPracticeBotRequestAction;
use Tests\TestCase;
use App\Models\ChallengeRequest;
use App\Services\PlayGame\StakingChallengeGameService;
use App\Actions\ActionHelpers\ChallengeRequestMatchHelper;
use App\Models\User;
use App\Repositories\Cashingames\TriviaQuestionRepository;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;

class MatchWithPracticeBotRequestActionTest extends TestCase
{
    public function test_that_challenge_request_is_always_matched_with_bot()
    {
        User::factory()
            ->count(5)
            ->hasProfile(1)
            ->hasWallet(1)
            ->create();

        $challengeRequest = ChallengeRequest::factory()->create([
            'status' => 'MATCHING',
        ]);
        $matchedRequest = ChallengeRequest::factory()->make([
            'user_id' => 1,
        ]);
        $mockedStakingChallengeGameService = $this->mockStakingChallengeGameService();
        $mockedStakingChallengeGameService
            ->expects($this->once())
            ->method('createPracticeRequest')
            ->with($this->isInstanceOf(User::class), $this->anything())
            ->willReturn($matchedRequest);

        $sut = new MatchWithPracticeBotRequestAction(
            $this->mockTriviaChallengeStakingRepository(),
            $this->mockTriviaQuestionRepository(),
            $mockedStakingChallengeGameService,
            $this->mockChallengeRequestMatchHelper(),
        );

        $result = $sut->execute($challengeRequest);

        $this->assertSame($matchedRequest->id, $result->id);
    }
    private function mockTriviaChallengeStakingRepository()
    {
        return $this->createMock(TriviaChallengeStakingRepository::class);
    }

    private function mockTriviaQuestionRepository()
    {
        return $this->createMock(TriviaQuestionRepository::class);
    }

    private function mockStakingChallengeGameService()
    {
        return $this->createMock(StakingChallengeGameService::class);
    }

    private function mockChallengeRequestMatchHelper()
    {
        return $this->createMock(ChallengeRequestMatchHelper::class);
    }

}
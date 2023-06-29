<?php

namespace Tests\Unit\ActionHelpers;

use App\Actions\TriviaChallenge\MatchRequestAction;
use App\Services\PlayGame\StakingChallengeGameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\ChallengeRequest;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use App\Repositories\Cashingames\TriviaQuestionRepository;

class MatchChallengeRequestActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_that_only_matching_requests_can_be_matches()
    {
        $sut = new MatchRequestAction(
            $this->mockTriviaChallengeStakingRepository(),
            $this->mockTriviaQuestionRepository(),
            $this->mockStakingChallengeGameService(),
        );

        $challengeRequest = ChallengeRequest::factory()->create();
        $result = $sut->execute($challengeRequest, 'testing');

        $this->assertNull($result);
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


}
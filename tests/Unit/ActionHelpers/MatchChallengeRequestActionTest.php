<?php

namespace Tests\Unit\ActionHelpers;

use Tests\TestCase;
use App\Models\User;
use App\Models\ChallengeRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Actions\TriviaChallenge\MatchRequestAction;
use App\Services\PlayGame\StakingChallengeGameService;
use App\Actions\ActionHelpers\ChallengeRequestMatchHelper;
use App\Repositories\Cashingames\TriviaQuestionRepository;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;

class MatchChallengeRequestActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_that_only_matching_requests_can_be_matches()
    {
        $sut = new MatchRequestAction(
            $this->mockTriviaChallengeStakingRepository(),
            $this->mockTriviaQuestionRepository(),
            $this->mockStakingChallengeGameService(),
            $this->mockChallengeRequestMatchHelper(),
        );

        $challengeRequest = ChallengeRequest::factory()->create();
        $result = $sut->execute($challengeRequest, 'testing');

        $this->assertNull($result);
    }

    public function test_that_challenge_request_is_matched_with_bot()
    {

        User::factory()->create([
            'id' => 1,
        ]);

        $challengeRequest = ChallengeRequest::factory()->create([
            'status' => 'MATCHING',
        ]);
        $matchedRequest = ChallengeRequest::factory()->make([
            'user_id' => 1,
        ]);
        $mockedStakingChallengeGameService = $this->mockStakingChallengeGameService();
        $mockedStakingChallengeGameService
            ->expects($this->once())
            ->method('create')
            ->with($this->isInstanceOf(User::class), $this->anything())
            ->willReturn($matchedRequest);

        $sut = new MatchRequestAction(
            $this->mockTriviaChallengeStakingRepository(),
            $this->mockTriviaQuestionRepository(),
            $mockedStakingChallengeGameService,
            $this->mockChallengeRequestMatchHelper(),
        );

        $result = $sut->execute($challengeRequest, 'testing');

        $this->assertSame($matchedRequest->id, $result->id);
    }

    public function test_that_challenge_request_is_matched_with_existing_request()
    {

        $challengeRequest = ChallengeRequest::factory()->create([
            'status' => 'MATCHING',
        ]);
        $matchedRequest = ChallengeRequest::factory()->create([
            'status' => 'MATCHING',
        ]);

        $mockedTriviaChallengeStakingRepository = $this->mockTriviaChallengeStakingRepository();
        $mockedTriviaChallengeStakingRepository
            ->expects($this->once())
            ->method('findMatch')
            ->with($challengeRequest)
            ->willReturn($matchedRequest);

        $sut = new MatchRequestAction(
            $mockedTriviaChallengeStakingRepository,
            $this->mockTriviaQuestionRepository(),
            $this->mockStakingChallengeGameService(),
            $this->mockChallengeRequestMatchHelper(),
        );

        $result = $sut->execute($challengeRequest, 'testing');

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
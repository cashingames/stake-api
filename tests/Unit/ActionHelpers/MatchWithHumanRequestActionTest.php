<?php

namespace Tests\Unit\ActionHelpers;

use App\Actions\TriviaChallenge\MatchWithHumanRequestAction;
use App\Services\Firebase\FirestoreService;
use Tests\TestCase;
use App\Models\ChallengeRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\PlayGame\StakingChallengeGameService;
use App\Actions\ActionHelpers\ChallengeRequestMatchHelper;
use App\Repositories\Cashingames\TriviaQuestionRepository;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;

class MatchWithHumanRequestActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_that_null_is_returned_when_no_match()
    {
        $sut = new MatchWithHumanRequestAction(
            $this->mockTriviaChallengeStakingRepository(),
            $this->mockTriviaQuestionRepository(),
            $this->mockStakingChallengeGameService(),
            $this->mockChallengeRequestMatchHelper(),
        );

        $challengeRequest = ChallengeRequest::factory()->create();
        $result = $sut->execute($challengeRequest);
        $this->assertNull($result);

    }

    public function test_that_challenge_request_is_matched_with_existing_request()
    {
        $this->instance(
            FirestoreService::class,
            $this->createMock(FirestoreService::class)
        );

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

        $sut = new MatchWithHumanRequestAction(
            $mockedTriviaChallengeStakingRepository,
            $this->mockTriviaQuestionRepository(),
            $this->mockStakingChallengeGameService(),
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
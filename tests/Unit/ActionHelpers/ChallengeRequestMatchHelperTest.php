<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Actions\ActionHelpers\ChallengeRequestMatchHelper;
use App\Models\ChallengeRequest;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use App\Repositories\Cashingames\TriviaQuestionRepository;
use App\Services\PlayGame\StakingChallengeGameService;

class ChallengeRequestMatchHelperTest extends TestCase
{

    public function test_questions_are_logged()
    {
        $challengeRequest = ChallengeRequest::factory()->make();
        $matchedRequest = ChallengeRequest::factory()->make();
        $questions = collect([
            ['question' => 'question 1'],
            ['question' => 'question 2'],
            ['question' => 'question 3'],
        ]);

        $triviaQuestionRepo = $this->mockTriviaQuestionRepository();
        $triviaQuestionRepo
            ->expects($this->once())
            ->method('getRandomEasyQuestionsWithCategoryId')
            ->with($challengeRequest->category_id)
            ->willReturn($questions);

        $triviaChallengeStakingRepo = $this->mockTriviaChallengeStakingRepository();
        $triviaChallengeStakingRepo
            ->expects($this->once())
            ->method('logQuestions')
            ->with($questions->toArray(), $challengeRequest, $matchedRequest);


        $sut = new ChallengeRequestMatchHelper(
            $triviaChallengeStakingRepo,
            $triviaQuestionRepo,
        );

        $result = $sut->processQuestions($challengeRequest, $matchedRequest);

        $this->assertEquals($questions, $result);

    }

    private function mockTriviaChallengeStakingRepository()
    {
        return $this->createMock(TriviaChallengeStakingRepository::class);
    }

    private function mockTriviaQuestionRepository()
    {
        return $this->createMock(TriviaQuestionRepository::class);
    }

}
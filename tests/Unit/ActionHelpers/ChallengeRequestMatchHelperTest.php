<?php

namespace Tests\Unit\ActionHelpers;

use App\Models\Profile;
use App\Models\Question;
use App\Services\Firebase\FirestoreService;
use App\Services\StakeQuestionsHardeningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Actions\ActionHelpers\ChallengeRequestMatchHelper;
use App\Models\ChallengeRequest;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use App\Repositories\Cashingames\TriviaQuestionRepository;
use App\Repositories\Cashingames\WalletRepository;

class ChallengeRequestMatchHelperTest extends TestCase
{
    use RefreshDatabase;

    public function test_questions_are_logged()
    {
        $challengeRequest = ChallengeRequest::factory()->make();
        $matchedRequest = ChallengeRequest::factory()->make();
        $questions = collect([
            ['question' => 'question 1'],
            ['question' => 'question 2'],
            ['question' => 'question 3'],
        ]);

        $triviaChallengeStakingRepo = $this->mockTriviaChallengeStakingRepository();
        $triviaChallengeStakingRepo
            ->expects($this->once())
            ->method('logQuestions')
            ->with($questions->toArray(), $challengeRequest, $matchedRequest);

        $stakeQuestionsHardeningService = $this->mockStakeQuestionsHardeningService();
        $stakeQuestionsHardeningService
            ->expects($this->once())
            ->method('determineQuestions')
            ->with($challengeRequest->user_id, $challengeRequest->category_id)
            ->willReturn($questions);

        $sut = new ChallengeRequestMatchHelper(
            $triviaChallengeStakingRepo,
            $this->mockTriviaQuestionRepository(),
            $stakeQuestionsHardeningService,
            $this->mockFirestoreService(),
            $this->mockWalletRepository(),
        );

        $result = $sut->processQuestions($challengeRequest, $matchedRequest);

        $this->assertEquals($questions, $result);

    }

    public function test_that_firestore_is_updated_successfully()
    {
        $profile1 = Profile::factory()->create();
        $profile2 = Profile::factory()->create();
        $challengeRequest = ChallengeRequest::factory()->for($profile1->user)->create();
        $matchedRequest = ChallengeRequest::factory()->for($profile2->user)->create();
        $questions = collect([
            Question::factory()->make(),
            Question::factory()->make(),
            Question::factory()->make(),
        ]);
        $firestoreService = $this->mockFirestoreService();
        $firestoreService
            ->expects($this->exactly(2))
            ->method('updateDocument')
            ->willReturnOnConsecutiveCalls(
                [
                    'trivia-challenge-requests',
                    $challengeRequest->challenge_request_id,
                    [
                        'status' => 'MATCHED',
                        'questions' => $this->anything(),
                        'opponent' => $this->anything(),
                    ]
                ],
                [
                    'trivia-challenge-requests',
                    $matchedRequest->challenge_request_id,
                    [
                        'status' => 'MATCHED',
                        'questions' => $this->anything(),
                        'opponent' => $this->anything(),
                    ]
                ]
            );


        $sut = new ChallengeRequestMatchHelper(
            $this->mockTriviaChallengeStakingRepository(),
            $this->mockTriviaQuestionRepository(),
            $this->mockStakeQuestionsHardeningService(),
            $firestoreService,
            $this->mockWalletRepository(),
        );

        $sut->updateFirestore($challengeRequest, $matchedRequest, $questions);
    }


    public function test_that_practice_questions_are_processed()
    {
        $challengeRequest = ChallengeRequest::factory()->make();
        $matchedRequest = ChallengeRequest::factory()->make();
        $questions = collect([
            ['question' => 'question 1'],
            ['question' => 'question 2'],
            ['question' => 'question 3'],
        ]);

        $triviaQuestionRepository = $this->mockTriviaQuestionRepository();
        $triviaQuestionRepository
            ->expects($this->once())
            ->method('getPracticeQuestionsWithCategoryId')
            ->with($challengeRequest->category_id)
            ->willReturn($questions);

        $triviaChallengeStakingRepo = $this->mockTriviaChallengeStakingRepository();
        $triviaChallengeStakingRepo
            ->expects($this->once())
            ->method('logQuestions')
            ->with($questions->toArray(), $challengeRequest, $matchedRequest);

        $sut = new ChallengeRequestMatchHelper(
            $triviaChallengeStakingRepo,
            $triviaQuestionRepository,
            $this->mockStakeQuestionsHardeningService(),
            $this->mockFirestoreService(),
            $this->mockWalletRepository()
        );

        $result = $sut->processPracticeQuestions($challengeRequest, $matchedRequest);

        $this->assertEquals($questions, $result);
    }


    private function mockTriviaChallengeStakingRepository()
    {
        return $this->createMock(TriviaChallengeStakingRepository::class);
    }

    private function mockWalletRepository()
    {
        return $this->createMock(WalletRepository::class);
    }

    private function mockTriviaQuestionRepository()
    {
        return $this->createMock(TriviaQuestionRepository::class);
    }

    private function mockFirestoreService()
    {
        return $this->createMock(FirestoreService::class);
    }

    private function mockStakeQuestionsHardeningService()
    {
        return $this->createMock(StakeQuestionsHardeningService::class);
    }

}
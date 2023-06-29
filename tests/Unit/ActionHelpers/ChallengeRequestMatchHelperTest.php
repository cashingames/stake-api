<?php

namespace Tests\Unit\ActionHelpers;

use App\Models\Profile;
use App\Models\Question;
use App\Services\Firebase\FirestoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Actions\ActionHelpers\ChallengeRequestMatchHelper;
use App\Models\ChallengeRequest;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use App\Repositories\Cashingames\TriviaQuestionRepository;

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
            $this->mockFirestoreService(),
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
            $firestoreService,
        );

        $sut->updateFirestore($challengeRequest, $matchedRequest, $questions);
    }

    private function mockTriviaChallengeStakingRepository()
    {
        return $this->createMock(TriviaChallengeStakingRepository::class);
    }

    private function mockTriviaQuestionRepository()
    {
        return $this->createMock(TriviaQuestionRepository::class);
    }

    private function mockFirestoreService()
    {
        return $this->createMock(FirestoreService::class);
    }

}
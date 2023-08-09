<?php

namespace Tests\Unit;

use App\Actions\ActionHelpers\ChallengeRequestMatchHelper;
use App\Actions\TriviaChallenge\MatchEndWalletAction;
use App\Actions\TriviaChallenge\PracticeMatchEndWalletAction;
use App\Enums\GameSessionStatus;
use App\Models\ChallengeRequest;
use App\Models\User;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use App\Repositories\Cashingames\WalletRepository;
use App\Services\Firebase\FirestoreService;
use App\Services\PlayGame\StakingChallengeGameService;
use Database\Seeders\BoostSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use Tests\TestCase;

class StakingChallengeGameServiceTest extends TestCase
{
    use RefreshDatabase;
    public $challengeRequest, $matchedRequest;
    public $data, $service;
    public $mockedTriviaChallengeStakingRepository, $mockedWalletRepository;
    public $mockedMatchEndWalletAction, $mockedFirestoreService;
    public $mockedPracticeMatchEndWalletAction, $mockedChallengeRequestMatchHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'challenge_request_id' => '1',
            'selected_options' => [],
            'consumed_boosts' => []
        ];

        $this->challengeRequest = ChallengeRequest::factory()->create([
            'user_id' => 1,
            'challenge_request_id' => '1',
            'session_token' => '123',
            'status' => 'COMPLETED',
            'amount' => 200,
            'amount_won' => 0,
            'ended_at' => now()->subMinute()
        ]);

        $this->matchedRequest = ChallengeRequest::factory()->create([
            'user_id' => 2,
            'challenge_request_id' => '2',
            'session_token' => '123',
            'status' => 'COMPLETED',
            'amount' => 200,
            'amount_won' => 0,
            'ended_at' => now()->subMinute()
        ]);

        $this->mockedTriviaChallengeStakingRepository = $this->mockTriviaChallengeStakingRepository();
        $this->mockedWalletRepository = $this->mockWalletRepository();
        $this->mockedMatchEndWalletAction = $this->mockMatchEndWalletAction();
        $this->mockedFirestoreService = $this->mockFireStoreService();
        $this->mockedPracticeMatchEndWalletAction = $this->mockPracticeMatchEndWalletAction();
        $this->mockedChallengeRequestMatchHelper =  $this->mockChallengeRequestMatchHelper();

        $this->service = new StakingChallengeGameService(
            $this->mockedWalletRepository,
            $this->mockedMatchEndWalletAction,
            $this->mockedFirestoreService,
            $this->mockedTriviaChallengeStakingRepository,
            $this->mockedPracticeMatchEndWalletAction,
            $this->mockedChallengeRequestMatchHelper
        );
    }
    public function test_that_message_is_logged_if_challenge_is_already_completed()
    {
        $this->mockedTriviaChallengeStakingRepository
            ->expects($this->once())
            ->method('getRequestById')
            ->with($this->challengeRequest->challenge_request_id)
            ->willReturn($this->challengeRequest);

        $result = $this->service->submit($this->data);
        $this->assertEquals($result, $this->challengeRequest);

        $logContents = file_get_contents(storage_path('logs/laravel-' . now()->toDateString() . '.log'));

        $this->assertStringContainsString('CHALLENGE_SUBMIT_ERROR', $logContents);
    }

    public function test_that_bot_submits_successfully()
    {

        $this->challengeRequest->update([
            'user_id' => '2',
            'status' => GameSessionStatus::MATCHED->value
        ]);
        $this->matchedRequest->update([
            'user_id' => '1',
            'status' => GameSessionStatus::MATCHED->value
        ]);

        $this->mockedTriviaChallengeStakingRepository
            ->expects($this->once())
            ->method('getRequestById')
            ->with($this->challengeRequest->challenge_request_id)
            ->willReturn($this->challengeRequest);

        $this->mockedTriviaChallengeStakingRepository
            ->expects($this->once())
            ->method('updateCompletedRequest')
            ->with($this->challengeRequest->challenge_request_id, $this->challengeRequest->score)
            ->willReturnOnConsecutiveCalls([$this->matchedRequest, $this->challengeRequest]);

        $this->mockedChallengeRequestMatchHelper
            ->expects($this->once())
            ->method('updateEndMatchFirestore')
            ->with($this->matchedRequest, $this->challengeRequest);

        $result = $this->service->submit($this->data);
        $this->assertEquals($result, $this->matchedRequest);
    }

    public function test_that_practice_bot_submits_successfully()
    {

        $this->challengeRequest->update([
            'user_id' => '2',
            'status' => GameSessionStatus::MATCHED->value
        ]);
        $this->matchedRequest->update([
            'user_id' => '1',
            'status' => GameSessionStatus::MATCHED->value
        ]);

        $this->mockedTriviaChallengeStakingRepository
            ->expects($this->once())
            ->method('getRequestById')
            ->with($this->challengeRequest->challenge_request_id)
            ->willReturn($this->challengeRequest);

        $this->mockedTriviaChallengeStakingRepository
            ->expects($this->once())
            ->method('updateCompletedRequest')
            ->with($this->challengeRequest->challenge_request_id, $this->challengeRequest->score)
            ->willReturnOnConsecutiveCalls([$this->matchedRequest, $this->challengeRequest]);

        $this->mockedChallengeRequestMatchHelper
            ->expects($this->once())
            ->method('updateEndMatchFirestore')
            ->with($this->matchedRequest, $this->challengeRequest);

        $result = $this->service->submit($this->data);
        $this->assertEquals($result, $this->matchedRequest);
    }

    public function test_that_message_is_logged_if_practice_challenge_is_already_completed()
    {
        $this->mockedTriviaChallengeStakingRepository
            ->expects($this->once())
            ->method('getRequestById')
            ->with($this->challengeRequest->challenge_request_id)
            ->willReturn($this->challengeRequest);

        $result = $this->service->submitPracticeChallenge($this->data);
        $this->assertEquals($result, $this->challengeRequest);

        $logContents = file_get_contents(storage_path('logs/laravel-' . now()->toDateString() . '.log'));

        $this->assertStringContainsString('PRACTICE_CHALLENGE_SUBMIT_ERROR', $logContents);
    }

    public function test_that_boosts_can_be_consumed_in_challenge_game()
    {
        User::factory()->create();
        $this->seed(BoostSeeder::class);

        DB::table('user_boosts')->insert(
            [
                'user_id' => 1,
                'boost_id' => 1,
                'boost_count' => 5,
                'used_count' => 0
            ]
        );
        $this->data['consumed_boosts'][0] = [
            'boost' =>
            ['id' => 1]
        ];

        $this->challengeRequest->update([
            'status' => GameSessionStatus::MATCHED->value
        ]);
        $this->matchedRequest->update([
            'status' => GameSessionStatus::MATCHED->value
        ]);

        $this->mockedTriviaChallengeStakingRepository
            ->expects($this->once())
            ->method('getRequestById')
            ->with($this->challengeRequest->challenge_request_id)
            ->willReturn($this->challengeRequest);

        $this->mockedTriviaChallengeStakingRepository
            ->expects($this->once())
            ->method('updateCompletedRequest')
            ->with($this->challengeRequest->challenge_request_id, $this->challengeRequest->score)
            ->willReturnOnConsecutiveCalls([$this->challengeRequest, $this->matchedRequest]);

        $this->mockedChallengeRequestMatchHelper
            ->expects($this->once())
            ->method('updateEndMatchFirestore')
            ->with($this->challengeRequest, $this->matchedRequest);

        $this->service->submit($this->data);
        $this->assertDatabaseHas('user_boosts', [
            'user_id' => 1,
            'boost_id' => 1,
            'boost_count' => 4,
            'used_count' => 1
        ]);
    }

    public function test_bot_submission_handler()
    {   
        $this->mockedTriviaChallengeStakingRepository
            ->expects($this->once())
            ->method('updateCompletedRequest');

        $botSubmissionHandler = self::getMethod('handleBotSubmission');
        $result = $botSubmissionHandler->invokeArgs(
            $this->service,
            array(
                $this->challengeRequest,
                $this->matchedRequest,
                4
            )
        );

        $this->assertIsArray($result);
    }

    public function test_practice_bot_submission_handler()
    {   
        $this->mockedTriviaChallengeStakingRepository
            ->expects($this->once())
            ->method('updateCompletedRequest');

        $botSubmissionHandler = self::getMethod('handlePracticeBotSubmission');
        $result = $botSubmissionHandler->invokeArgs(
            $this->service,
            array(
                $this->challengeRequest,
                4
            )
        );

        $this->assertIsArray($result);
    }

    public function test_generate_bot_score_handler()
    {   
        $user = User::factory()->create();

        $this->mockedWalletRepository
            ->expects($this->once())
            ->method('getUserProfitPercentageOnStakingThisYear')
            ->with($user->id)
            ->willReturn(50);

        $botScoreHandler = self::getMethod('generateBotScore');
        $result = $botScoreHandler->invokeArgs(
            $this->service,
            array(
                $user->id,
                5
            )
        );

        $this->assertIsFloat($result);
    }


    protected static function getMethod($name)
    {
        $class = new ReflectionClass(StakingChallengeGameService::class);
        $method = $class->getMethod($name);
        return $method;
    }
    private function mockTriviaChallengeStakingRepository()
    {
        return $this->createMock(TriviaChallengeStakingRepository::class);
    }

    private function mockWalletRepository()
    {
        return $this->createMock(WalletRepository::class);
    }
    private function mockMatchEndWalletAction()
    {
        return $this->createMock(MatchEndWalletAction::class);
    }
    private function mockFireStoreService()
    {
        return $this->createMock(FirestoreService::class);
    }
    private function mockPracticeMatchEndWalletAction()
    {
        return $this->createMock(PracticeMatchEndWalletAction::class);
    }
    private function mockChallengeRequestMatchHelper()
    {
        return $this->createMock(ChallengeRequestMatchHelper::class);
    }
}

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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
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

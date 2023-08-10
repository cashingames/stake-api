<?php

namespace Tests\Unit\ActionHelpers;

use App\Actions\ActionHelpers\ChallengeRequestMatchHelper;
use App\Actions\TriviaChallenge\MatchEndWalletAction;
use App\Models\ChallengeRequest;
use App\Models\Wallet;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use App\Repositories\Cashingames\WalletRepository;
use App\Services\Firebase\FirestoreService;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Mockery\MockInterface;
use Tests\TestCase;

class MatchEndWalletActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->mock(FirestoreService::class, function (MockInterface $mock) {
            $mock->shouldReceive('updateDocument');
        });
    }


    public function cannotBeEndedDataProvider()
    {
        return [
            ['MATCHING', 'MATCHING'],
            ['MATCHED', 'MATCHING'],
            ['MATCHED', 'MATCHED'],
            ['MATCHED', 'COMPLETED'],
            ['COMPLETED', 'MATCHED'],
        ];
    }

    /**
     * @dataProvider cannotBeEndedDataProvider
     */
    public function test_that_match_cannot_be_ended_when_both_match_is_not_completed(
        string $challengeStatus,
        string $matchedChallengeStatus
    ) {

        $challengeRequest = ChallengeRequest::factory()->create([
            'user_id' => 1,
            'status' => $challengeStatus,
        ]);

        $matchedRequest = ChallengeRequest::factory()->create([
            'user_id' => 2,
            'status' => $matchedChallengeStatus,
        ]);

        $mockedTriviaChallengeStakingRepository = $this->mockTriviaChallengeStakingRepository();
        $mockedTriviaChallengeStakingRepository
            ->expects($this->once())
            ->method('getRequestById')
            ->with($challengeRequest->id)
            ->willReturn($challengeRequest);
        $mockedTriviaChallengeStakingRepository
            ->expects($this->once())
            ->method('getMatchedRequestById')
            ->with($challengeRequest->id)
            ->willReturn($matchedRequest);

        $sut = new MatchEndWalletAction(
            $mockedTriviaChallengeStakingRepository,
            $this->mockWalletRepository(),
            $this->mockChallengeRequestMatchHelper(),
        );

        $result = $sut->execute($challengeRequest->id);

        $this->assertNull($result);
    }

    public function test_that_we_initiate_refund_and_sent_notification_when_both_have_same_score()
    {
        Notification::fake();

        $wallet = Wallet::factory()->create();
        $challengeRequest = ChallengeRequest::factory()->for($wallet->user)->create([
            'status' => 'COMPLETED',
            'session_token' => '1234',
            'score' => 10,
            'amount' => 1000,
            'ended_at' => now()
        ]);

        $wallet2 = Wallet::factory()->create();
        $matchedRequest = ChallengeRequest::factory()->for($wallet2->user)->create([
            'status' => 'COMPLETED',
            'session_token' => '1234',
            'score' => 10,
            'amount' => 1000,
            'ended_at' => now()
        ]);

        $sut = new MatchEndWalletAction(
            app()->make(TriviaChallengeStakingRepository::class),
            app()->make(WalletRepository::class),
            app()->make(ChallengeRequestMatchHelper::class),
        );

        $result = $sut->execute($challengeRequest->challenge_request_id);

        $this->assertNull($result);
        Notification::assertCount(2);
    }

    public function test_that_winner_is_credited_when_game_ends()
    {
        $wallet = Wallet::factory()->create();
        $challengeRequest = ChallengeRequest::factory()->for($wallet->user)->create([
            'status' => 'COMPLETED',
            'challenge_request_id' => '123',
            'session_token' => '1234',
            'score' => 10,
            'amount' => 1000
        ]);
       
        $wallet2 = Wallet::factory()->create();
        $matchedRequest = ChallengeRequest::factory()->for($wallet2->user)->create([
            'status' => 'COMPLETED',
            'challenge_request_id' => '456',
            'session_token' => '1234',
            'score' => 2,
            'amount' => 1000
        ]);

        $sut = new MatchEndWalletAction(
            app()->make(TriviaChallengeStakingRepository::class),
            app()->make(WalletRepository::class),
            app()->make(ChallengeRequestMatchHelper::class),
        );
        $result = $sut->execute($challengeRequest->challenge_request_id);

        $this->assertSame($challengeRequest->id, $result->id);
        $this->assertNotSame($matchedRequest->id, $result->id);
    }

    public function test_that_loser_is_not_credited_when_game_ends()
    {
        $wallet = Wallet::factory()->create();
        $challengeRequest = ChallengeRequest::factory()->for($wallet->user)->create([
            'status' => 'COMPLETED',
            'challenge_request_id' => '123',
            'session_token' => '1234',
            'score' => 2,
            'amount' => 1000
        ]);
       
        $wallet2 = Wallet::factory()->create();
        $matchedRequest = ChallengeRequest::factory()->for($wallet2->user)->create([
            'status' => 'COMPLETED',
            'challenge_request_id' => '456',
            'session_token' => '1234',
            'score' => 5,
            'amount' => 1000
        ]);

        $sut = new MatchEndWalletAction(
            app()->make(TriviaChallengeStakingRepository::class),
            app()->make(WalletRepository::class),
            app()->make(ChallengeRequestMatchHelper::class),
        );
        $result = $sut->execute($challengeRequest->challenge_request_id);

        $this->assertSame($matchedRequest->id, $result->id);
        $this->assertNotSame($challengeRequest->id, $result->id);
    }

    private function mockTriviaChallengeStakingRepository()
    {
        return $this->createMock(TriviaChallengeStakingRepository::class);
    }

    private function mockWalletRepository()
    {
        return $this->createMock(WalletRepository::class);
    }

    private function mockChallengeRequestMatchHelper()
    {
        return $this->createMock(ChallengeRequestMatchHelper::class);
    }
}

<?php

namespace Tests\Unit\ActionHelpers;

use App\Actions\TriviaChallenge\MatchEndWalletAction;
use App\Models\ChallengeRequest;
use App\Models\Wallet;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use App\Repositories\Cashingames\WalletRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MatchEndWalletActionTest extends TestCase
{
    use RefreshDatabase;

    public function cannotBeEndedDataProvider()
    {
        return [
            ['MATCHING', 'MATCHING'],
            ['MATCHED', 'MATCHING'],
            ['MATCHED', 'MATCHED'],
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
            'status' => $challengeStatus,
        ]);

        $matchedRequest = ChallengeRequest::factory()->create([
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
        );

        $result = $sut->execute($challengeRequest->id);

        $this->assertNull($result);
    }

    public function test_that_a_winner_is_auto_selected_if_one_opponent_fails_to_end(
       
    ) {

        $challengeRequest = ChallengeRequest::factory()->create([
            'user_id' => 1,
            'status' => 'COMPLETED',
            'session_token' => 'tyruh4878475',
            'ended_at' => now()->subMinute()
        ]);
        $matchedRequest = ChallengeRequest::factory()->create([
            'user_id' => 2,
            'status' => "MATCHED",
            'session_token' => $challengeRequest->session_token,
            'ended_at' => null
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
        );

        $result = $sut->execute($challengeRequest->id);

        $this->assertEquals($challengeRequest->id,  $result->id);
    }

    public function test_that_we_initiate_refund_and_sent_notification_when_both_have_same_score()
    {
        Notification::fake();

        $wallet = Wallet::factory()->create();
        $challengeRequest = ChallengeRequest::factory()->for($wallet->user)->create([
            'status' => 'COMPLETED',
            'score' => 10,
            'amount' => 1000,
        ]);

        $wallet2 = Wallet::factory()->create();
        $matchedRequest = ChallengeRequest::factory()->for($wallet2->user)->create([
            'status' => 'COMPLETED',
            'score' => 10,
            'amount' => 1000,
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

        $mockedCreditWalletAction = $this->mockWalletRepository();
        $mockedCreditWalletAction
            ->expects($this->exactly(2))
            ->method('addTransaction');

        $sut = new MatchEndWalletAction(
            $mockedTriviaChallengeStakingRepository,
            $mockedCreditWalletAction,
        );

        $result = $sut->execute($challengeRequest->id);

        $this->assertNull($result);
        Notification::assertCount(2);
    }

    public function test_that_winner_is_credited_when_game_ends()
    {
        $wallet = Wallet::factory()->create();
        $challengeRequest = ChallengeRequest::factory()->for($wallet->user)->create([
            'status' => 'COMPLETED',
            'score' => 10,
            'amount' => 1000,
        ]);

        $wallet2 = Wallet::factory()->create();
        $matchedRequest = ChallengeRequest::factory()->for($wallet2->user)->create([
            'status' => 'COMPLETED',
            'score' => 2,
            'amount' => 1000,
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

        $mockedCreditWalletAction = $this->mockWalletRepository();
        $mockedCreditWalletAction
            ->expects($this->once())
            ->method('addTransaction');

        $sut = new MatchEndWalletAction(
            $mockedTriviaChallengeStakingRepository,
            $mockedCreditWalletAction,
        );

        $result = $sut->execute($challengeRequest->id);

        $this->assertSame($challengeRequest->id, $result->id);
    }

    private function mockTriviaChallengeStakingRepository()
    {
        return $this->createMock(TriviaChallengeStakingRepository::class);
    }

    private function mockWalletRepository()
    {
        return $this->createMock(WalletRepository::class);
    }
}

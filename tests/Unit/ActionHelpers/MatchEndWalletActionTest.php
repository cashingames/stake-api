<?php

namespace Tests\Unit\ActionHelpers;

use App\Actions\TriviaChallenge\MatchEndWalletAction;
use App\Actions\Wallet\CreditWalletAction;
use App\Models\ChallengeRequest;
use App\Models\Wallet;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
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
            ['MATCHED', 'COMPLETED'],
            ['COMPLETED', 'MATCHED'],
        ];
    }

    /**
     * @dataProvider cannotBeEndedDataProvider
     */
    public function test_that_match_cannot_be_ended_when_both_match_is_not_completed
    (
        string $challengeStatus, string $matchedChallengeStatus
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
            $this->mockCreditWalletAction(),
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

        $mockedCreditWalletAction = $this->mockCreditWalletAction();
        $mockedCreditWalletAction
            ->expects($this->exactly(2))
            ->method('executeRefund')
            ->with($this->anything(), $challengeRequest->amount, $this->anything());

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

        $mockedCreditWalletAction = $this->mockCreditWalletAction();
        $mockedCreditWalletAction
            ->expects($this->once())
            ->method('execute')
            ->with($this->anything(), $challengeRequest->amount * 2, $this->anything());

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

    private function mockCreditWalletAction()
    {
        return $this->createMock(CreditWalletAction::class);
    }


}
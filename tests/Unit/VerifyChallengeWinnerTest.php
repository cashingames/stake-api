<?php

namespace Tests\Unit;

use App\Actions\ActionHelpers\ChallengeRequestMatchHelper;
use App\Actions\TriviaChallenge\VerifyChallengeWinnerAction;
use App\Jobs\VerifyChallengeWinner;
use App\Models\ChallengeRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class VerifyChallengeWinnerTest extends TestCase
{
    use RefreshDatabase;
    public function test_that_user_is_selected_as_winner_if_opponent_does_not_submit()
    {
        $this->instance(
            ChallengeRequestMatchHelper::class,
            Mockery::mock(ChallengeRequestMatchHelper::class, function (MockInterface $mock) {
                $mock->shouldReceive('updateEndMatchFirestore')->once();
            })
        );

        User::factory()
            ->count(5)
            ->hasProfile(1)
            ->hasWallet(1)
            ->create();

        $challengeRequest = ChallengeRequest::factory()->create([
            'user_id' => 1,
            'session_token' => '123',
            'status' => 'COMPLETED',
            'amount' => 200,
            'amount_won' => 0,
            'ended_at' => now()->subMinute()
        ]);
        $matchedRequest = ChallengeRequest::factory()->create([
            'user_id' => 2,
            'session_token' => '123',
            'status' => "MATCHED",
            'amount' => 200,
            'amount_won' => 0,
            'ended_at' => null
        ]);

        $verifyWinnerAction = app()->make(VerifyChallengeWinnerAction::class);

        $verifyWinnerJob = new VerifyChallengeWinner(
            $challengeRequest,
            $matchedRequest
        );

        $verifyWinnerJob->handle($verifyWinnerAction);

        $this->assertDatabaseHas('challenge_requests', [
            'user_id' => '1',
            'status' => 'COMPLETED',
        ]);
        $this->assertDatabaseHas('challenge_requests', [
            'user_id' => '2',
            'status' => 'SYSTEM_COMPLETED',
        ]);


        $this->assertEquals($challengeRequest->amount_won,  $challengeRequest->amount * 2);
    }
}

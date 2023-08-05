<?php

namespace Tests\Unit;

use App\Models\ChallengeRequest;
use App\Models\User;
use Tests\TestCase;

class SetDefaultChallengeWinnerTest extends TestCase
{
    public function test_that_user_is_selected_as_winner_if_opponent_does_not_submit()
    {
        User::factory()
            ->count(5)
            ->hasProfile(1)
            ->hasWallet(1)
            ->create();

        $challengeRequest = ChallengeRequest::factory()->create([
            'user_id' => 1,
            'status' => 'COMPLETED',
            'ended_at' => now()->subMinutes(1)
        ]);
        $matchedRequest = ChallengeRequest::factory()->make([
            'user_id' => 2,
            'status' => "MATCHED",
            'ended_at' => null
        ]);

        $setwinnerjob = new \App\Actions\TriviaChallenge\SetDefaultChallengeWinner(
            $challengeRequest,
            $matchedRequest
        );

        $winner = $setwinnerjob->execute($challengeRequest);

        $this->assertEquals($challengeRequest,  $winner);
    }
}

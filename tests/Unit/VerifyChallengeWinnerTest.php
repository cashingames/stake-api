<?php

namespace Tests\Unit;

use App\Actions\TriviaChallenge\VerifyChallengeWinnerAction;
use App\Jobs\VerifyChallengeWinner;
use App\Models\ChallengeRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Repositories\Cashingames\WalletRepository;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;

class VerifyChallengeWinnerTest extends TestCase
{   
    use RefreshDatabase;
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
            'amount' => 200,
            'amount_won' => 0,
            'ended_at' => now()->subMinute()
        ]);
        $matchedRequest = ChallengeRequest::factory()->make([
            'user_id' => 2,
            'status' => "MATCHED",
            'amount' => 200,
            'amount_won' => 0,
            'ended_at' => null
        ]);

        $walletRepository = new WalletRepository;
        $triviaChallengeRepository = new TriviaChallengeStakingRepository;

        $verifyWinnerAction= new VerifyChallengeWinnerAction( $triviaChallengeRepository , $walletRepository);

        $verifyWinnerJob = new VerifyChallengeWinner(
            $challengeRequest,
            $matchedRequest,
            $verifyWinnerAction
        );

        $verifyWinnerJob->handle();

        $this->assertEquals($challengeRequest->amount_won,  $challengeRequest->amount * 2);
    }
}

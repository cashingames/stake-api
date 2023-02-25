<?php

namespace Tests\Feature;

use App\Models\ExhibitionStaking;
use App\Models\GameSession;
use App\Models\Profile;
use App\Models\Staking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// eval(\Psy\sh());

class GetStakersSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $profiles = Profile::factory()
            ->count(4)
            ->create();

        $this->stakingSessionsSetup($profiles->find(1), 7, 50, 0); //less than staked
        $this->stakingSessionsSetup($profiles->find(2), 7, 50, 150); //more than staked
        $this->stakingSessionsSetup($profiles->find(3), 7, 50, 50); //equal to staked
        $this->stakingSessionsSetup($profiles->find(4), 2, 50, 150); //more than staked and low score

        config(['features.exhibition_game_staking.enabled' => true]);
    }

    private function stakingSessionsSetup(Profile $profile, int $correctCount, int $amountStaked, int $amountWon)
    {
        $session = GameSession::factory()
            ->for($profile->user)
            ->create([
                'correct_count' => $correctCount,
            ]);
        $staking = Staking::factory()
            ->for($profile->user)
            ->create([
                'amount_staked' => $amountStaked,
                'amount_won' => $amountWon,
            ]);
        ExhibitionStaking::factory()
            ->for($session)
            ->for($staking)
            ->create();
    }

    public function test_recent_staking_winners_won_more_than_staked()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/api/v3/stakers/sessions/recent');
        $response->assertJsonCount(2, '*');

    }
}
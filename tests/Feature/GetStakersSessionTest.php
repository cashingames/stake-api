<?php

namespace Tests\Feature;

use App\Models\ExhibitionStaking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetStakersSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ExhibitionStaking::factory()
            ->count(3)
            ->create();

        config(['features.exhibition_game_staking.enabled' => true]);
        config(['features.trivia_game_staking.enabled' => true]);
    }
    public function test_recent_stakers_sessions_can_be_gotten()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/api/v3/stakers/sessions/recent');

        // $r = $response->json();
        // // eval(\Psy\sh());

        $response->assertJsonCount(0, '*');

    }
}
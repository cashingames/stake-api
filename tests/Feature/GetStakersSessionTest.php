<?php

namespace Tests\Feature;

use App\Models\GameType;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\GameModeSeeder;
use Database\Seeders\GameTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetStakersSessionTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    // private $category;
    // private $plan;

    protected function setUp(): void
    {
        parent::setUp();

        // $this->seed(CategorySeeder::class);
        // $this->seed(GameTypeSeeder::class);
        // $this->seed(GameModeSeeder::class);

        User::factory()
            ->count(3)
            ->hasProfile(1)
            ->hasGameSessions(1)
            ->hasStakings(1)
            ->create();

        //             ->hasStakings(1)
        // ->hasExhibitionStakings(1)
        //seeded this because it doesn't have dependency on other tables
        //and we don't need to control the data

        // $this->seed(PlanSeeder::class);
        // GameSession::factory()
        //     ->count(20)
        //     ->create();

        // GameSession::factory()
        //     ->count(20)
        //     ->create();
        // $this->user = User::first();
        // $this->actingAs($this->user);
        config(['features.exhibition_game_staking.enabled' => true]);
        config(['features.trivia_game_staking.enabled' => true]);
    }
    public function test_recent_stakers_sessions_can_be_gotten()
    {
        $user = User::factory()->create();

        // // eval(\Psy\sh());

        $response = $this->actingAs($user)->get('/api/v3/stakers/sessions/recent');


        $response->assertJsonCount(0, '*');

    }
}
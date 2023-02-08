<?php

namespace Tests\Feature;

use App\Models\ExhibitionStaking;
use App\Models\GameSession;
use App\Models\Staking;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\GameModeSeeder;
use Database\Seeders\GameTypeSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\UserSeeder;
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

    public $user, $category , $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        $this->seed(GameTypeSeeder::class);
        $this->seed(GameModeSeeder::class);
        $this->seed(PlanSeeder::class);
        GameSession::factory()
            ->count(20)
            ->create();
        $this->user = User::first();
        $this->actingAs($this->user);
        config(['features.exhibition_game_staking.enabled' => true]);
        config(['features.trivia_game_staking.enabled' => true]);
    }
    public function test_recent_stakers_sessions_can_be_gotten()
    {
        $staking = Staking::create([
            'user_id' => $this->user->id,
            'amount_staked' => 1000,
            'odd_applied_during_staking' => 3.0
        ]);

        ExhibitionStaking::create([
            'staking_id' => $staking->id,
            'game_session_id' => GameSession::first()->id
        ]);
        $response = $this->get('/api/v3/stakers/sessions/recent');

        $response->assertJsonStructure([
            [
                "id",
                "username",
                "avatar",
                "correct_count",
                "amount_won",
            ]
        ]);
    }
}

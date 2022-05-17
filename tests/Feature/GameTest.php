<?php

namespace Tests\Feature;

use App\Models\GameSession;
use App\Models\User;
use App\Models\UserPoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use CategorySeeder;
use AchievementSeeder;
use App\Models\Achievement;
use BoostSeeder;
use PlanSeeder;
use GameTypeSeeder;
use GameModeSeeder;

class GameTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);
        $this->seed(AchievementSeeder::class);
        $this->seed(BoostSeeder::class);
        $this->seed(GameTypeSeeder::class);
        $this->seed(GameModeSeeder::class);
        $this->seed(PlanSeeder::class);
        GameSession::factory()
            ->count(20)
            ->create();

        $this->user = User::first();

        $this->actingAs($this->user);
    }

    public function test_common_data_can_be_retrieved()
    {
        $response = $this->get('/api/v3/game/common');

        $response->assertStatus(200);
    }

    public function test_common_data_can_be_retrieved_with_data()
    {
        $response = $this->get('/api/v3/game/common');

        $response->assertJsonStructure([
            'data' => [
                'achievements' => [],
                'boosts' => [],
                'plans' => [],
                'gameModes' => [],
                'gameTypes' => [],
                'minVersionCode' => [],
                'hasLiveTrivia' => []
            ]
        ]);
    }

    public function test_achievement_can_be_claimed()
    {
        $achievement = Achievement::first();
        
        UserPoint::create([
            'user_id' => $this->user->id,
            'value' => 5000,
            'description'=> 'Test points added',
            'point_flow_type'=>'POINTS_ADDED'
        ]);

        $response = $this->post('/api/v2/claim/achievement/' . $achievement->id);

        $response->assertStatus(200);
    }

    public function test_achievement_cannot_be_claimed_if_points_are_not_enough()
    {
        $achievement = Achievement::first();
        
        $response = $this->post('/api/v2/claim/achievement/' . $achievement->id);

        $response->assertJson([
            'errors' => 'You do not have enough points to claim this achievement',
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\GameSession;
use App\Models\Question;
use App\Models\User;
use App\Models\UserPlan;
use App\Models\Plan;
use App\Models\UserPoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use CategorySeeder;
use UserSeeder;
use AchievementSeeder;
use App\Models\Achievement;
use App\Models\Category;
use BoostSeeder;
use PlanSeeder;
use GameTypeSeeder;
use GameModeSeeder;
use Illuminate\Support\Carbon;

class GameTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    const COMMON_DATA_URL = '/api/v3/game/common';
    const CLAIM_ACHIEVEMENT_URL = '/api/v2/claim/achievement/';
    const START_SINGLE_GAME_URL = '/api/v2/game/start/single-player';

    protected $user;
    protected $category;
    protected $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
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
        $this->category = Category::where('category_id', '!=', 0)->inRandomOrder()->first();
        $this->plan = Plan::inRandomOrder()->first();
        $this->actingAs($this->user);
    }

    public function test_common_data_can_be_retrieved()
    {
        $response = $this->get(self::COMMON_DATA_URL);

        $response->assertStatus(200);
    }

    public function test_common_data_can_be_retrieved_with_data()
    {
        $response = $this->get(self::COMMON_DATA_URL);

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

        $response = $this->post(self::CLAIM_ACHIEVEMENT_URL.$achievement->id);

        $response->assertStatus(200);
    }

    public function test_achievement_cannot_be_claimed_if_points_are_not_enough()
    {
        $achievement = Achievement::first();
        
        $response = $this->post(self::CLAIM_ACHIEVEMENT_URL.$achievement->id);

        $response->assertJson([
            'errors' => 'You do not have enough points to claim this achievement',
        ]);
    }

    public function test_single_game_can_be_started(){
        Question::factory()
        ->count(50)
        ->create();
        
        UserPlan::create([
            'plan_id' => $this->plan->id,
            'user_id' => $this->user->id,
            'used_count' => 0,
            'plan_count' =>1,
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'expire_at' => Carbon::now()->endOfDay()
        ]);

        $response = $this->postjson(self::START_SINGLE_GAME_URL, [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2
        ]);
        $response->assertJson([
            'message' => 'Game Started',
        ]);
    }
}

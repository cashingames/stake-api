<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use AchievementSeeder;
use AchievementBadgeSeeder;
use BoostSeeder;
use UserSeeder;
use PlanSeeder;
use App\Models\Achievement;
use App\Models\Boost;
use App\Models\User;
use App\Models\Plan;
use App\Models\UserPoint;

class StoreTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    const GAME_COMMON_DATA_URL = '/api/v3/game/common';
    const BUY_ITEM_URL = '/api/v3/purchased/item';
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
        $this->seed(AchievementBadgeSeeder::class);
        $this->user = User::inRandomOrder()->first();

        $this->actingAs($this->user);
    }

    //Boost Test Cases
    public function test_boosts_can_be_fetched()
    {
        $this->seed(BoostSeeder::class);
        $response = $this->get(self::GAME_COMMON_DATA_URL);

        $response->assertJsonStructure([
            'data' => [
                'boosts',
            ]
        ]);
        $response->assertStatus(200);
    }

    //Plans Test Cases
    public function test_plans_can_be_fetched()
    {
        $this->seed(PlanSeeder::class);
        $response = $this->get(self::GAME_COMMON_DATA_URL);

        $response->assertJsonStructure([
            'data' => [
                'plans',
            ]
        ]);
    }


    public function test_gameark_inapp_item_can_be_bought()
    {

        $this->seed(BoostSeeder::class);
        $this->seed(PlanSeeder::class);

        $response = $this->withHeaders(['x-brand-id'=> 10])->postjson(self::BUY_ITEM_URL, [
            "type" => 'plan',
            "item_id" => Boost::inRandomOrder()->first()->id
        ]);

        $response->assertStatus(200);
    }
}

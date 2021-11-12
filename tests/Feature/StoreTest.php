<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use AchievementSeeder;
use BoostSeeder;
use UserSeeder;
use App\Models\Achievement;
use App\Models\Boost;
use App\Models\User;
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
    const CLAIM_ACHIEVEMENT_URL = '/api/v2/claim/achievement/';
    const BUY_BOOST_POINTS_URL = '/api/v2/points/buy-boosts/';
    const BUY_BOOST_WALLET_URL = '/api/v2/wallet/buy-boosts/';
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
        $this->user = User::inRandomOrder()->first();

        $this->actingAs($this->user);
    }

    //Achievement Test Cases
    public function test_achievements_can_be_fetched()
    {   
        $this->seed(AchievementSeeder::class);
        $response = $this->get(self::GAME_COMMON_DATA_URL);

        $response->assertJsonStructure([
            'data' => [
                'achievements',
            ]
        ]);
        $response->assertStatus(200);
    }

    public function test_achievement_must_first_exist_to_be_claimed()
    {   
        $this->seed(AchievementSeeder::class);

        $response = $this->post(self::CLAIM_ACHIEVEMENT_URL.'50');
        $response->assertJsonFragment(['message' => 'Invalid Achievement']);

        $response->assertStatus(400);
    }

    public function test_achievement_can_be_claimed()
    {   
        $this->seed(AchievementSeeder::class);
        UserPoint::create([
            'user_id' => $this->user->id,
            'value' => 200000,
            'description'=> 'test points added',
            'point_flow_type'=>'POINTS_ADDED'
        ]);

        $response = $this->post(self::CLAIM_ACHIEVEMENT_URL.Achievement::inRandomOrder()->first()->id);
        $response->assertJsonFragment(['message' => 'Achievement Claimed']);

        $response->assertStatus(200);
    }

    public function test_achievement_cannot_be_claimed_if_point_is_less_than_achievement_point_milestone()
    {   
        $this->seed(AchievementSeeder::class);

        $response = $this->post(self::CLAIM_ACHIEVEMENT_URL.Achievement::inRandomOrder()->first()->id);
        $response->assertJsonFragment(['message' => 'You do not have enough points to claim this achievement']);

        $response->assertStatus(400);
    }

    public function test_achievement_cannot_be_claimed_more_than_once()
    {   
         UserPoint::create([
            'user_id' => $this->user->id,
            'value' => 2000,
            'description'=> 'test points added',
            'point_flow_type'=>'POINTS_ADDED'
        ]);

        $this->seed(AchievementSeeder::class);
        $achievement = Achievement::first();
        $this->post(self::CLAIM_ACHIEVEMENT_URL.$achievement->id);

        $secondAchievementClaimAttempt =  $this->post(self::CLAIM_ACHIEVEMENT_URL.$achievement->id);
        $secondAchievementClaimAttempt->assertJsonFragment(['message' => 'You have already claimed this achievement']);

        $secondAchievementClaimAttempt->assertStatus(400);
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

    public function test_a_boost_must_first_exist_to_be_bought()
    {   
        $this->seed(BoostSeeder::class);

        $response = $this->post(self::BUY_BOOST_WALLET_URL.'50');
        $response->assertJsonFragment(['message' => 'Wrong boost selected']);

        $response->assertStatus(400);
    }

    public function test_boosts_can_be_bought_from_wallet()
    {   
        $this->seed(BoostSeeder::class);
        $this->user->wallet->update(['balance'=> 1000]);

        $response = $this->post(self::BUY_BOOST_WALLET_URL.Boost::inRandomOrder()->first()->id);
        $response->assertJsonFragment(['message' => 'Boost Bought']);

        $response->assertStatus(200);
    }

    public function test_boost_can_be_bought_with_points()
    {   
        UserPoint::create([
            'user_id' => $this->user->id,
            'value' => 2000,
            'description'=> 'test points added',
            'point_flow_type'=>'POINTS_ADDED'
        ]);

        $this->seed(BoostSeeder::class);

        $response = $this->post(self::BUY_BOOST_POINTS_URL.Boost::inRandomOrder()->first()->id);
        
        // $response->dump();
        $response->assertStatus(200);
    }

    public function test_boost_cannot_be_bought_if_point_is_less_than_boost_point_value()
    {   
        $this->seed(BoostSeeder::class);

        $response = $this->post(self::BUY_BOOST_POINTS_URL.Boost::inRandomOrder()->first()->id);
        $response->assertJsonFragment(['message' => 'You do not have enough points']);

        $response->assertStatus(400);
    }

    public function test_boost_cannot_be_bought_if_wallet_balance_is_less_than_boost_currency_value()
    {   
        $this->seed(BoostSeeder::class);

        $response = $this->post(self::BUY_BOOST_WALLET_URL.Boost::inRandomOrder()->first()->id);
        $response->assertJsonFragment(['message' => 'You do not have enough money in your wallet.']);

        $response->assertStatus(400);
    }

}

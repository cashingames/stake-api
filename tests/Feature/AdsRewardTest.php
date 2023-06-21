<?php

namespace Tests\Feature;

use App\Models\Boost;
use App\Models\User;
use Database\Seeders\BoostSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdsRewardTest extends TestCase
{
    /**
     * A basic feature test example.
     */
   
     use RefreshDatabase, WithFaker;

     protected $user;
     protected function setUp(): void
     {
         parent::setUp();
         
         $this->seed(UserSeeder::class);
         $this->seed(BoostSeeder::class);
         $this->user = User::first();
         $this->actingAs($this->user);
     }
     
     public function test_user_receieves_boost_ads_reward()
     {
        $response = $this->post('/api/v3/ads-reward/award', [
            'adRewardType' => 'boost',
            'rewardCount' => 4,
            'adRewardPrize' => 'Time Freeze'
        ]);

        $this->assertDatabaseHas('user_boosts', [
            'user_id' => $this->user->id,
            'boost_id' => Boost::where('name', 'Time Freeze')->first()->id,
            'boost_count' => 4,
        ]);
     }

     public function test_user_receieves_coins_ads_reward()
     {
        $response = $this->post('/api/v3/ads-reward/award', [
            'adRewardType' => 'coins',
            'rewardCount' => 30,
            'adRewardPrize' => 'coins'
        ]);

        $this->assertDatabaseHas('user_coins', [
            'user_id' => $this->user->id,
            'coins_value' => 30,
        ]);
     }
}

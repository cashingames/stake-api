<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserReward;
use Carbon\Carbon;
use Database\Seeders\RewardSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DismissUserRewardTest extends TestCase
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
        $this->seed(RewardSeeder::class);
        $this->user = User::first();
        $this->actingAs($this->user);
    }

    public function test_a_user_can_dismiss_reward()
    {

        UserReward::create([
            'user_id' => $this->user->id,
            'reward_id' => 1,
            'reward_count' => 0,
            'reward_date' => Carbon::now(),
            'release_on' => Carbon::now(),
            'reward_milestone' => 1,
        ]);

        $response = $this->post('/api/v3/dismiss/user-reward');

        $this->assertDatabaseHas('user_rewards', [
            'user_id' => $this->user->id,
            'reward_count' => -1,
        ]);
    }
}

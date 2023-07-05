<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserReward;
use App\Services\DailyRewardService;
use Carbon\Carbon;
use Database\Seeders\RewardSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MissUserRewardTest extends TestCase
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

    public function test_a_user_can_miss_reward()
    {
        UserReward::create([
            'user_id' => $this->user->id,
            'reward_id' => 1,
            'reward_count' => 0,
            'reward_date' => Carbon::now(),
            'release_on' => Carbon::now(),
            'reward_milestone' => 1,
        ]);
        UserReward::create([
            'user_id' => $this->user->id,
            'reward_id' => 1,
            'reward_count' => 1,
            'reward_date' => Carbon::now(),
            'release_on' => Carbon::now(),
            'reward_milestone' => 1,
        ]);

        $this->post('/api/v3/user-reward/miss');

        $this->assertDatabaseHas('user_rewards', [
            'user_id' => $this->user->id,
            'reward_count' => -1,
        ]);
        $this->assertDatabaseHas('user_rewards', [
            'user_id' => $this->user->id,
            'reward_count' => 1,
        ]);
    }

    public function test_a_user_reward_record_resets_when_a_day_is_missed()
    {
        UserReward::create([
            'user_id' => $this->user->id,
            'reward_id' => 1,
            'reward_count' => 0,
            'reward_date' => Carbon::now()->subDays(3),
            'release_on' => Carbon::now(),
            'reward_milestone' => 1,
        ]);
        UserReward::create([
            'user_id' => $this->user->id,
            'reward_id' => 1,
            'reward_count' => 1,
            'reward_date' =>  Carbon::now()->subDays(2),
            'release_on' => Carbon::now(),
            'reward_milestone' => 1,
        ]);
        $service = new DailyRewardService();
        $service->shouldShowDailyReward($this->user);

        $this->assertDatabaseHas('user_rewards', [
            'user_id' => $this->user->id,
            'reward_count' => -1,
        ]);
        $this->assertDatabaseHas('user_rewards', [
            'user_id' => $this->user->id,
            'reward_count' => 1,
            'deleted_at' => now()
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Boost;
use App\Models\RewardBenefit;
use App\Models\User;
use App\Models\UserReward;
use Carbon\Carbon;
use Carbon\Factory;
use Database\Seeders\BoostSeeder;
use Database\Seeders\RewardBenefitSeeder;
use Database\Seeders\RewardSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClaimUserRewardTest extends TestCase
{

    use RefreshDatabase, WithFaker;
    /**
     * A basic feature test example.
     */
    protected $user; protected $singleReward;
    // protected $reward;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(RewardSeeder::class);
        $this->seed(RewardBenefitSeeder::class);
        $this->seed(BoostSeeder::class);
        $this->user = User::first();
        $this->actingAs($this->user);
    }

    public function test_a_user_can_claim_reward()
    {

        UserReward::create([
            'user_id' => $this->user->id,
            'reward_id' => 1,
            'reward_count' => 0,
            'reward_date' => Carbon::now(),
            'release_on' => Carbon::now(),
            'reward_milestone' => 1,
        ]);

        $response = $this->post('/api/v3/claim/user-reward');

        $this->assertDatabaseHas('user_rewards', [
            'user_id' => $this->user->id,
            'reward_count' => 1,
        ]);
    }

    public function test_a_user_gets_rewarded_with_boost_daily_reward()
    {
        UserReward::create([
            'user_id' => $this->user->id,
            'reward_id' => 1,
            'reward_count' => 0,
            'reward_date' => Carbon::now(),
            'release_on' => Carbon::now(),
            'reward_milestone' => 1,
        ]);

        $rewardBenefit = RewardBenefit::first();
        $response = $this->post('/api/v3/claim/user-reward');

        $this->assertDatabaseHas('user_boosts', [
            'user_id' => $this->user->id,
            'boost_id' => Boost::where('name', $rewardBenefit->reward_name)->first()->id,
            'boost_count' => $rewardBenefit->reward_count,
        ]);
    }

    public function test_a_user_gets_rewarded_with_coins_daily_reward()
    {
       UserReward::factory()
       ->count(4)
       ->create([
            'user_id' => $this->user->id,
            'reward_id' => 1,
            'reward_count' => 0,
            'reward_date' => Carbon::now(),
            'release_on' => Carbon::now(),
            'reward_milestone' => 1,
        ]);
        $userRewardRecordCount = $this->user->rewards()->count();

        $rewardBenefit = RewardBenefit::where('reward_benefit_id', $userRewardRecordCount)->first();

        $response = $this->post('/api/v3/claim/user-reward');
        $this->assertDatabaseHas('user_coins', [
            'user_id' => $this->user->id,
            'coins_value' =>$rewardBenefit->reward_count,
        ]);
    }
    
    public function test_a_user_rewards_gets_a_new_record_after_claiming_daily_reward()
    {
        UserReward::create([
            'user_id' => $this->user->id,
            'reward_id' => 1,
            'reward_count' => 0,
            'reward_date' => Carbon::now(),
            'release_on' => Carbon::now(),
            'reward_milestone' => 1,
        ]);

        $userRewardRecordCount = $this->user->rewards()->count();
        $response = $this->post('/api/v3/claim/user-reward');

        $this->assertDatabaseHas('user_rewards', [
            'user_id' => $this->user->id,
            'reward_count' => 0,
            'reward_milestone' => $userRewardRecordCount + 1
        ]);
    }

}

<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\BoostSeeder;
use Database\Seeders\RewardBenefitSeeder;
use Database\Seeders\RewardSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClaimHallmarkLevelRewardsTest extends TestCase
{

    use RefreshDatabase, WithFaker;
    public $user;

    const CLAIM_HALLMARK_LEVEL_REWARD_URL = '/api/v3/levels-reward/claim';

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

    public function test_that_endpoint_is_successful()
    {
        $level = 4;
        $response = $this->post(self::CLAIM_HALLMARK_LEVEL_REWARD_URL, [
            'level' => $level,
        ]);

        $response->assertStatus(200);
    }

    public function test_that_user_gets_level_reward()
    {
        $level = 4;
        $response = $this->post(self::CLAIM_HALLMARK_LEVEL_REWARD_URL, [
            'level' => $level,
        ]);

        $this->assertDatabaseHas('user_boosts', [
            'user_id' => $this->user->id,
            'boost_id' => 2,
            'boost_count' => $level,
        ]);

    }

    public function test_that_new_user_gets_level_reward()
    {
        // create new user
        $newUser = User::create([
            'username' => 'testUser',
            'phone_number' => '08133445858',
            'email' => 'testaccount@gmail.com',
            'password' => 'xcvb',
            'otp_token' => '2134',
            'is_on_line' => true,
            'country_code' => 'NGN',
        ]);
        $newUser
            ->profile()
            ->create([
                'first_name' => 'zxcv',
                'last_name' => 'asdasd',
                'referral_code' => 'zxczxc',
                'referrer' => $this->user->profile->referrer,
            ]);
        $this->actingAs($newUser);

        $level = 1;
        $response = $this->post(self::CLAIM_HALLMARK_LEVEL_REWARD_URL, [
            'level' => $level,
        ]);

        $this->assertDatabaseHas('user_boosts', [
            'user_id' => $newUser->id,
            'boost_id' => 3,
            'boost_count' => $level,
        ]);
    }
}

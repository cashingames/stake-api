<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserReward;
use Database\Seeders\RewardSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use UserSeeder;
use PlanSeeder;

class ArtisanCommandsTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    private $user ;
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(PlanSeeder::class);
        $this->user = User::first();
    }


    public function test_give_daily_bonus_command()
    {
        $this->artisan('bonus:daily-activate')->assertExitCode(0);
    }

    public function test_expire_daily_bonus_command()
    {
        $this->artisan('bonus:daily-expire')->assertExitCode(0);
    }

    public function test_refresh_daily_bonus_command()
    {
        $this->artisan('bonus:refresh')->assertExitCode(0);
    }

    public function test_daily_morning_reminder_command()
    {
        $this->artisan('fcm:daily-morning-reminder')->assertExitCode(0);
    }

    public function test_daily_afternoon_reminder_command()
    {
        $this->artisan('fcm:daily-afternoon-reminder')->assertExitCode(0);
    }

    public function test_daily_evening_reminder_command()
    {
        $this->artisan('fcm:daily-evening-reminder')->assertExitCode(0);
    }

    public function test_contraint_reminder_reminder_command()
    {
        $this->artisan('fcm:inactive-user-reminder')->assertExitCode(0);
    }

    public function test_daily_reward_reactivation_command_reactivates_reward()
    {   
        $this->seed(RewardSeeder::class);  
        
        UserReward::create([
            'user_id' => $this->user->id,
            'reward_id' => 1,
            'reward_count' => -1,
            'reward_date' => now(),
            'release_on' => now(),
            'reward_milestone' => 3,
        ]);

        $this->artisan('user-reward:reactivate')->assertExitCode(0);

        $this->assertDatabaseHas('user_rewards', [
            'user_id' => $this->user->id,
            'reward_count' => 0,
            'reward_milestone' => 1,
        ]);
    }

}

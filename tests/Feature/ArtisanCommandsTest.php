<?php

namespace Tests\Feature;

use App\Mail\DailyReportEmail;
use App\Mail\WeeklyReportEmail;
use App\Models\ChallengeRequest;
use App\Models\GameSession;
use App\Models\Staking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(PlanSeeder::class);
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

}

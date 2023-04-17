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

    public function test_credit_winnings()
    {
        $this->artisan('winnings:credit')->assertExitCode(0);
    }

    // public function test_trigger_special_hour()
    // {
    //     $this->artisan('odds:special-hour')->assertExitCode(0);
    // }

    // public function test_trigger_live_trivia_notification()
    // {
    //     $this->artisan('live-trivia:notify')->assertExitCode(0);
    // }

    // public function test_boost_reminder_notifications()
    // {
    //     $this->artisan('boosts:send-notification')->assertExitCode(0);
    // }

    public function test_that_daily_automated_report_command_runs()
    {
        Mail::fake();

        $this->artisan('daily-report:send')->assertExitCode(0);

        Mail::assertSent(DailyReportEmail::class);
    }

    public function test_that_weekly_automated_report_command_runs()
    {
        GameSession::factory()
            ->count(5)
            ->create(['created_at' => now()->yesterday()]);

        Staking::factory()
            ->count(5)
            ->create(['created_at' => now()->yesterday()]);

        Mail::fake();

        $this->artisan('weekly-report:send')->assertExitCode(0);

        Mail::assertSent(WeeklyReportEmail::class);
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

}

<?php

namespace Tests\Feature;

use App\Enums\BonusType;
use App\Mail\DailyReportEmail;
use App\Mail\WeeklyReportEmail;
use App\Models\Bonus;
use App\Models\ChallengeRequest;
use App\Models\GameSession;
use App\Models\Staking;
use App\Models\User;
use App\Models\UserBonus;
use Database\Seeders\BonusSeeder;
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
        // $this->seed(PlanSeeder::class);
    }

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
    
    public function test_that_registration_bonuses_can_be_expired(){
        config(['features.registration_bonus.enabled' => true]);
        $this->seed(BonusSeeder::class);
        $user = User::first();

        $user->wallet->update(['bonus' => 500, 'withdrawable' => 1000]);

        UserBonus::create([
            'user_id' => $user->id,
            'bonus_id' =>  Bonus::where('name', BonusType::RegistrationBonus->value)->first()->id,
            'is_on' => true,
            'amount_credited' => 1500,
            'amount_remaining_after_staking' => 500,
            'total_amount_won'  => 1000,
            'amount_remaining_after_withdrawal' => 1000
        ]);

        $bonus = UserBonus::first();
        $bonus->created_at = now()->subDays(10);
        $bonus->save();

        $this->artisan('registration-bonus:expire')->assertExitCode(0);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'bonus' => 0,
            'withdrawable' => 0
        ]);

        $this->assertDatabaseHas('user_bonuses', [
            'user_id' => $user->id,
            'total_amount_won'  => 1000,
            'is_on' => false,
            'amount_remaining_after_staking' => 500,
            'amount_remaining_after_withdrawal' => 1000,
        ]);
    }
  


}

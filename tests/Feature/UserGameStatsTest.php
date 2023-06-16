<?php

namespace Tests\Feature;

use App\Mail\UserGameStatsEmail;
use App\Models\GameSession;
use App\Models\User;
use App\Services\UserGameStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserGameStatsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public $userStatsService;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()
            ->count(10)
            ->create(['created_at' => now('Africa/Lagos')->yesterday()]);
        GameSession::factory()
            ->count(10)
            ->create(['created_at' => now('Africa/Lagos')->yesterday()]);
        $this->user = User::first();
        $this->actingAs($this->user);
        $this->userStatsService = new UserGameStatsService();
    }

    public function test_that_user_biWeekly_game_stats_returns_data()
    {
        $dailyReports = $this->userStatsService->getBiWeeklyUserGameStats($this->user);
        $this->assertCount(6, $dailyReports);
    }
    public function test_that_send_user_game_stats_command_runs()
    {
        Mail::fake();

        $this->artisan('app:send-user-game-stats-email')
        ->assertExitCode(0);

        Mail::assertSent(UserGameStatsEmail::class);
    }

}

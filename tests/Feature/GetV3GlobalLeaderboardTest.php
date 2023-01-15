<?php

namespace Tests\Feature;

use App\Models\GameSession;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\GameModeSeeder;
use Database\Seeders\GameTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GetV3GlobalWeeklyLeaderboardTest extends TestCase
{
    use RefreshDatabase;

    const GLOBAL_LEADERS_URL = '/api/v3/leaders/global/';
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        $this->seed(PlanSeeder::class);
        $this->seed(GameModeSeeder::class);
        $this->seed(GameTypeSeeder::class);
        $this->user = User::first();

        GameSession::factory()
            ->count(5)
            ->create([
                'user_id' => $this->user->id,
                'points_gained' => 10,
                'created_at' => Carbon::today()->subDays(2)
            ]);

        GameSession::factory()
            ->count(5)
            ->create([
                'user_id' => 2,
                'points_gained' => 8,
                'created_at' => Carbon::today()->subDays(1)
            ]);


        $this->actingAs($this->user);
    }

    public function test_global_leaderboard_should_return_data_based_on_date_range_if_date_range_filter_is_passed()
    {

        $startDate = Carbon::today()->subDays(2);
        $endDate = Carbon::now();

        $response = $this->postjson(self::GLOBAL_LEADERS_URL, [
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);

        $response->assertJsonCount(2, 'data.leaderboard');
        $response->assertStatus(200);
    }

    public function test_global_leaderboard_returns_current_user_correct_rank()
    {
        $startDate = Carbon::today()->subDays(2);
        $endDate = Carbon::now();

        $response = $this->postjson(self::GLOBAL_LEADERS_URL, [
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);

        $this->assertEquals($response->getData()->data->userRank->rank, 1);

    }

    public function test_global_leaderboard_returns_current_user_points()
    {
        $startDate = Carbon::today()->subDays(2);
        $endDate = Carbon::now();

        $response = $this->postjson(self::GLOBAL_LEADERS_URL, [
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);

        $points = $this->user->gameSessions()->sum('points_gained');
       
        $this->assertEquals($response->getData()->data->userRank->points, $points);

    }
}

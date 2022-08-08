<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use UserSeeder;
use Database\Seeders\CategorySeeder;
use App\Models\GameSession;
use App\Models\User;
use Carbon\Carbon;

class LeaderboardTest extends TestCase
{
    use RefreshDatabase;

    const GLOBAL_LEADERS_URL = '/api/v2/leaders/global/';
    const CATEGORIES_LEADERS_URL = '/api/v2/leaders/categories/';
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        $this->user = User::first();
        GameSession::factory()
            ->count(20)
            ->create();

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
        $response->assertJsonCount(5, 'data');
        $response->assertStatus(200);
    }

    

    public function test_categories_leaderboard_should_return_data_based_on_date_range_if_date_range_filter_is_passed()
    {
        // $this->seed(CategorySeeder::class);

        $startDate = Carbon::today()->subDays(2);
        $endDate = Carbon::now()->addMinute();

        $response = $this->post(self::CATEGORIES_LEADERS_URL, [
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);

        $response->assertJsonCount(2, 'data');
    }

    public function test_global_leaders_can_be_fetched_by_post_method_without_dates()
    {

        $response = $this->post(self::GLOBAL_LEADERS_URL);
        $response->assertStatus(200);
    }

    public function test_global_leaders_can_be_fetched_by_post_method_with_dates()
    {

        $response = $this->post(self::GLOBAL_LEADERS_URL, [
            'startDate' => Carbon::today()->startOfDay('Africa/Lagos'),
            'endDate' => Carbon::tomorrow()->startOfDay('Africa/Lagos')
        ]);
        $response->assertStatus(200);
    }
    public function test_categories_leaders_can_be_fetched_by_post_method_without_dates()
    {

        $response = $this->post(self::CATEGORIES_LEADERS_URL);
        $response->assertStatus(200);
    }

    public function test_categories_leaders_can_be_fetched_by_post_method_with_dates()
    {

        $response = $this->post(self::CATEGORIES_LEADERS_URL, [
            'startDate' => Carbon::today()->startOfDay('Africa/Lagos'),
            'endDate' => Carbon::tomorrow()->startOfDay('Africa/Lagos')
        ]);
        $response->assertStatus(200);
    }
}

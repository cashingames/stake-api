<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use UserSeeder;
use CategorySeeder;
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
        $this->user = User::first();

        $this->actingAs($this->user);
    }

    // public function test_global_leaderboard_should_return_data_since_inception_if_no_filter_date_is_passed(){

    //     $response = $this->get(self::GLOBAL_LEADERS_URL);


    //     $response->assertJsonCount(5, 'data');

    // }

    public function test_global_leaderboard_should_return_data_based_on_date_range_if_date_range_filter_is_passed()
    {

        $startDate = Carbon::today()->subDays(1);
        $endDate = Carbon::now();

        $response = $this->get(self::GLOBAL_LEADERS_URL . $startDate . '/' . $endDate);
        $response->assertJsonCount(5, 'data');
    }

    public function test_categories_leaderboard_should_return_data_since_inception_if_no_filter_date_is_passed()
    {
        // $this->seed(CategorySeeder::class);
        $response = $this->get(self::CATEGORIES_LEADERS_URL);

        $response->assertJsonCount(0, 'data.*.*');
    }

    public function test_categories_leaderboard_should_return_data_based_on_date_range_if_date_range_filter_is_passed()
    {
        // $this->seed(CategorySeeder::class);

        $startDate = Carbon::today()->subDays(1);
        $endDate = Carbon::now()->addMinute();

        $response = $this->get(self::CATEGORIES_LEADERS_URL . $startDate . '/' . $endDate);

        $response->assertJsonCount(0, 'data.*.*');
    }

    public function test_global_leaders_can_be_fetched_by_post_method_without_dates(){
       
        $response = $this->post(self::GLOBAL_LEADERS_URL);
        $response->assertStatus(200);
    }

    public function test_global_leaders_can_be_fetched_by_post_method_with_dates(){
       
        $response = $this->post(self::GLOBAL_LEADERS_URL,[
            'startDate' => Carbon::today()->startOfDay('Africa/Lagos'),
            'endDate' => Carbon::tomorrow()->startOfDay('Africa/Lagos')
        ]);
        $response->assertStatus(200);
    }
    public function test_categories_leaders_can_be_fetched_by_post_method_without_dates(){
       
        $response = $this->post(self::CATEGORIES_LEADERS_URL);
        $response->assertStatus(200);
    }

    public function test_categories_leaders_can_be_fetched_by_post_method_with_dates(){
       
        $response = $this->post(self::CATEGORIES_LEADERS_URL,[
            'startDate' => Carbon::today()->startOfDay('Africa/Lagos'),
            'endDate' => Carbon::tomorrow()->startOfDay('Africa/Lagos')
        ]);
        $response->assertStatus(200);
    }
}

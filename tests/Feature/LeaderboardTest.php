<?php

namespace Tests\Feature;

use App\Models\GameSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use UserSeeder;
use App\Models\User;
use Carbon\Carbon;

class LeaderboardTest extends TestCase
{   
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    const GLOBAL_LEADERS_URL = '/api/v2/leaders/global/';
    const CATEGORIES_LEADERS_URL = '/api/v2/leaders/categories/';
    protected $user;

    protected function setUp(): void{
        parent::setUp();
        
        $this->seed(UserSeeder::class);
        $this->user = User::first(); 

        $this->actingAs($this->user);

    }

    public function test_global_leaderboard_should_return_data_since_inception_if_no_filter_date_is_passed(){
   
        $response = $this->get(self::GLOBAL_LEADERS_URL);

        
        $response->assertJsonCount(5, 'data');
        
    }

    public function test_global_leaderboard_should_return_data_based_on_date_range_if_date_range_filter_is_passed(){
     
        $startDate = Carbon::today()->subDays(rand(0, 365));
        $endDate = Carbon::today()->addDays(rand(0, 365));

        $response = $this->get(self::GLOBAL_LEADERS_URL.$startDate.'/'.$endDate);
        $response->assertJsonCount(5, 'data');
    }

    public function test_categories_leaderboard_should_return_data_since_inception_if_no_filter_date_is_passed(){
     

        $response = $this->get(self::CATEGORIES_LEADERS_URL);

        $response->dump();
        $response->assertJsonCount(2, 'data');
    }

    public function test_categories_leaderboard_should_return_data_based_on_date_range_if_date_range_filter_is_passed(){
     
        $startDate = Carbon::today()->subDays(rand(0, 365));
        $endDate = Carbon::today()->addDays(rand(0, 365));

        $response = $this->get(self::CATEGORIES_LEADERS_URL.$startDate.'/'.$endDate);
        $response->dump();
        $response->assertJsonCount(2, 'data');
    }
}

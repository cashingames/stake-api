<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\Category;
use App\Models\LiveTrivia;
use App\Models\UserPoint;
use Database\Seeders\UserSeeder;
use Database\Seeders\CategorySeeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class LiveTriviaStatusTest extends TestCase
{   
    use RefreshDatabase;

    protected $user;
    
    const LIVE_TRIVIA_STATUS_URL = '/api/v3/live-trivia/status';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        $this->user = User::first();
        $this->category = Category::where('category_id', '!=', 0)->inRandomOrder()->first();
        $this->actingAs($this->user);

        UserPoint::create([
            'user_id' => $this->user->id,
            'value' => 500,
            'description'=> 'test points',
            'point_flow_type'=>'POINTS_ADDED',
            'created_at' => Carbon::now(),
        ]);

    }

    public function test_that_live_trivia_status_endpoint_returns_data()
    {   
       
        $this->createTestLiveTrivia(Carbon::now(),Carbon::now()->addHour());

        $response = $this->get(self::LIVE_TRIVIA_STATUS_URL);

        $response->assertJsonStructure([
            "id",
            "name",
            "category_id",
            "game_mode_id",
            "game_type_id",
            "point_eligibility",
            "grand_price",
            "created_at",
            "updated_at",
            "game_duration",
            "question_count",
            "start_time",
            "end_time",
            "deleted_at",
            "is_published",
            "status",
            "start_time_utc",
            "player_status"
        ]);
    }

    public function test_that_live_trivia_status_endpoint_returns_trivia_waiting_status()
    {   

        $this->createTestLiveTrivia(Carbon::now()->addHour(),Carbon::now()->addHours(2));
        $response = $this->get(self::LIVE_TRIVIA_STATUS_URL);

        $response->assertJsonFragment([
            "status" =>"WAITING",
        ]);
    }

    public function test_that_live_trivia_status_endpoint_returns_trivia_ongoing_status()
    {  
        $this->createTestLiveTrivia(Carbon::now(),Carbon::now()->addHour());
        $response = $this->get(self::LIVE_TRIVIA_STATUS_URL);

        $response->assertJsonFragment([
            "status" =>"ONGOING",
        ]);
    }

    public function test_that_live_trivia_status_endpoint_returns_trivia_closed_status()
    {   
        Config::set('trivia.live_trivia.display_shelf_life', 1);
      
        $this->createTestLiveTrivia(Carbon::now()->subHour(), Carbon::now());
        $response = $this->get(self::LIVE_TRIVIA_STATUS_URL);

        $response->assertJsonFragment([
            "status" =>"CLOSED",
        ]);
    }

    private function createTestLiveTrivia($start,$end){
        LiveTrivia::create([
            "name" => "Test Live Trivia",
            "category_id" => $this->category->id,
            'game_mode_id' => 1,
            'game_type_id'=>2,
            'grand_price'=> 1000,
            'point_eligibility'=>500,
            'is_published'=> true,
            'start_time' => $start,
            'end_time' => $end
        ]);

    }
  
}
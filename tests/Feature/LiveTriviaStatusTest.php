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
use Database\Seeders\LiveTriviaSeeder;
use Carbon\Carbon;
use Illuminate\Support\Carbon as SupportCarbon;

class LiveTriviaStatusTest extends TestCase
{   
    use RefreshDatabase;
    protected $user, $liveTrivia, $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        $this->seed(LiveTriviaSeeder::class);
        // $this->seed(GameTypeSeeder::class);
        // $this->seed(GameModeSeeder::class);
        $this->user = User::first();
        $this->liveTrivia = LiveTrivia::inRandomOrder()->first();
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
        var_dump(UserPoint::today()->first()->getCurrentUserPoints());
        die();
       $live= LiveTrivia::create([
            "name" => "Test Live Trivia",
            "category_id" => $this->category->id,
            'game_mode_id' => 1,
            'game_type_id'=>2,
            'grand_price'=> 1000,
            'point_eligibility'=>500,
            'start_time' => Carbon::now(),
            'end_time' => Carbon::now()->addHour()
        ]);
        $response = $this->get('/api/v3/live-trivia/status');

        $response->dump($live);
    }
}

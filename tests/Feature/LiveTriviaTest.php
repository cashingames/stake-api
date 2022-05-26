<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Seeders\TriviaSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\CategorySeeder;
use GameTypeSeeder;
use GameModeSeeder;
use App\Models\Question;
use App\Models\Category;
use App\Models\User;
use App\Models\Trivia;
use App\Models\GameSession;
use Illuminate\Support\Carbon;
use Ramsey\Uuid\Type\Integer;

class LiveTriviaTest extends TestCase
{   
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // use RefreshDatabase;

    protected $user, $trivia, $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        $this->seed(TriviaSeeder::class);
        $this->seed(GameTypeSeeder::class);
        $this->seed(GameModeSeeder::class);
        $this->user = User::first();
        $this->trivia = Trivia::inRandomOrder()->first();
        $this->category = Category::where('category_id', '!=', 0)->inRandomOrder()->first();
        $this->actingAs($this->user);
    }

    public function test_live_trivia_can_be_fetched(){
        $response = $this->get('/api/v3/fetch/trivia');

        $response->assertStatus(200);
    }

    public function test_no_trivia_can_be_fetched_if_not_published()
    {   
        $response = $this->get('/api/v3/fetch/trivia');

        $response->assertJson([
            'data' => [],
        ]);
    }

    public function test_trivia_can_only_be_fetched_if_published()
    {   
        $this->trivia->update(['is_published'=>true]);

        $response = $this->get('/api/v3/fetch/trivia');

        $response->assertJsonCount(1, $key = 'data');
    }

    public function test_live_trivia_can_be_started(){
        Question::factory()
        ->count(50)
        ->create();

        $response = $this->postjson('/api/v2/game/start/single-player', [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2,
            "trivia" => $this->trivia->id
        ]);
        $response->assertJson([
            'message' => 'Game Started',
        ]);
      
    }

    public function test_live_trivia_leaders_can_be_fetched(){
        GameSession::factory()
        ->count(20)
        ->create();

        $response = $this->get('/api/v3/trivia/leaders/'.$this->trivia->id);
        
            
        $response->assertJson([
            'data' => [
                "leaders" =>[],
            ],
        ]);
      
    }

  
  
}

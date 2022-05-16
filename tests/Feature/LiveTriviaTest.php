<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Seeders\TriviaSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\CategorySeeder;
use App\Models\User;
use App\Models\Trivia;

class LiveTriviaTest extends TestCase
{   
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        $this->seed(TriviaSeeder::class);
        $this->user = User::first();
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
        Trivia::first()->update(['is_published'=>true]);

        $response = $this->get('/api/v3/fetch/trivia');

        $response->assertJsonCount(1, $key = 'data');
    }


    public function test_live_trivia_can_be_created(){
        $response = $this->post('/api/v3/trivia/create',[
            'name' => 'Test Trivia',
            'category' => 'Naija Music',
            'grand_price' => 1000,
            'point_eligibility' => 0,
            'start_time' => '2022/05/06 02:04:00',
            'end_time' => '2022/05/07 02:04:00',
            'game_duration' => 180,
            'question_count' => 30
        ]);

        $response->assertStatus(200);
    }
  
}

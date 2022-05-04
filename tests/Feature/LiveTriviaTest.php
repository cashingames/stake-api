<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Seeders\TriviaSeeder;
use Database\Seeders\UserSeeder;
use App\Models\User;
use App\Models\Trivia;

class LiveTriviaTest extends TestCase
{
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
        $this->seed(TriviaSeeder::class);
        $this->user = User::first();
        $this->actingAs($this->user);
    }

    public function test_no_trivia_can_be_fetched_if_not_published()
    {   
        $response = $this->get('/api/v3/fetch/trivia');

        $response->assertJson([
            'data' => [],
        ]);;
    }

    public function test_trivia_can_only_be_fetched_if_published()
    {   
        Trivia::first()->update(['is_published'=>true]);

        $response = $this->get('/api/v3/fetch/trivia');

        $response->assertJsonCount(1, $key = 'data');
    }

  
}

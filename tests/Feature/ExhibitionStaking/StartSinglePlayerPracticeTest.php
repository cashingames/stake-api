<?php

namespace Tests\Feature\ExhibitionStaking;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StartSinglePlayerPracticeTest extends TestCase
{   
    use RefreshDatabase;
    const URL = '/api/v3/single-player/practice/start';
    private $user;
    private $category;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create();
        $this->seed(CategorySeeder::class);

        $this->user = User::first();
        $this->category = Category::first();

        $this->actingAs($this->user);
    }
    public function test_that_single_player_practice_game_starts(): void
    {   
      
        $response =  $this->postjson(self::URL, [
            "category" => $this->category->id,
            "amount" => 200
        ]);

        $response->assertJsonStructure([
            'data' => [
                'questions' ,
                'game'
            ]
        ]);
    }
}

<?php

namespace Tests\Feature\ExhibitionStaking;

use App\Models\Category;
use App\Models\Question;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EndExhibitionPracticeGameTest extends TestCase
{
    use RefreshDatabase;
    const URL = '/api/v3/single-player/practice/end';
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
    public function test_that_single_player_practice_game_returns_relevant_data(): void
    {

        $questions = Question::factory()
            ->hasOptions(4)
            ->count(10)
            ->create();
        $chosenOptions = [];
        foreach ($questions as $question) {
            $chosenOptions[] = $question->options()->inRandomOrder()->first();
        }

        $points = collect($chosenOptions)->filter(function ($value) {
            return $value->is_correct == 1;
        })->count();

        $response =  $this->postjson(self::URL, [
            "chosenOptions" =>  $chosenOptions,
        ]);

        $response->assertJson([
            'data' => [
                'points_gained' => $points,
                'correct_count' => $points,
                'total_count' => count($chosenOptions),
                'wrong_count' => count($chosenOptions) - $points
            ]
        ]);
    }

   
}

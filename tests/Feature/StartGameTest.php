<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Plan;
use App\Models\Question;
use App\Models\Trivia;
use App\Models\User;
use App\Models\UserPlan;
use Carbon\Carbon;
use Database\Seeders\CategorySeeder;
use Database\Seeders\GameModeSeeder;
use Database\Seeders\GameTypeSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\TriviaSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StartGameTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $category;
    private $plan;
    const START_EXHIBITION_GAME_URL = '/api/v3/game/start/single-player';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        $this->seed(GameTypeSeeder::class);
        $this->seed(GameModeSeeder::class);
        $this->seed(PlanSeeder::class);

        $this->user = User::first();
        $this->category = Category::first();
        $this->plan = Plan::first();

        $this->actingAs($this->user);
    }

    public function test_exhibition_game_can_be_started_for_a_new_user()
    {
        $questions = Question::factory()
            ->count(250)
            ->state(
                new Sequence(
                    ['level' => 'easy'],
                )
            )
            ->create();

        $data = [];

        foreach ($questions as $question) {
            $data[] = [
                'question_id' => $question->id,
                'category_id' => $this->category->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('categories_questions')->insert($data);

        UserPlan::create([
            'plan_id' => $this->plan->id,
            'user_id' => $this->user->id,
            'used_count' => 0,
            'plan_count' => 1,
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'expire_at' => Carbon::now()->endOfDay()
        ]);

        $response = $this->postjson(self::START_EXHIBITION_GAME_URL, [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2
        ]);
        $response->assertJson([
            'message' => 'Game Started',
        ]);
    }

    public function test_livetrivia_game_can_be_started_for_a_new_user()
    {
        $questions = Question::factory()
            ->count(250)
            ->create();

        $this->seed(TriviaSeeder::class);

        $data = [];

        foreach ($questions as $question) {
            $data[] = [
                'question_id' => $question->id,
                'category_id' => $this->category->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('categories_questions')->insert($data);

        $response = $this->postjson(self::START_EXHIBITION_GAME_URL, [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2,
            "trivia" => Trivia::first()->id
        ]);

        $response->assertJson([
            'message' => 'Game Started',
        ]);
    }
}

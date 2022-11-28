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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
    const RECENT_LIVE_TRIVIA_URL = '/api/v3/live-trivia/recent';

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

    public function test_live_trivia_can_be_fetched()
    {
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
        $this->trivia->update(['is_published' => true]);

        $response = $this->get('/api/v3/fetch/trivia');

        $response->assertJsonCount(1, $key = 'data');
    }

    public function test_live_trivia_can_be_started()
    {
        $questions = Question::factory()
            ->count(250)
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

    public function test_live_trivia_cannot_be_played_more_than_once_by_the_same_user()
    {
        $questions = Question::factory()
            ->count(250)
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
        
        GameSession::create([
            'category_id' => 101,
            'trivia_id' => $this->trivia->id,
            'game_mode_id' => 2,
            'game_type_id' => 2,
            'plan_id' => 1,
            'user_id' => $this->user->id,
            'start_time' => Carbon::now(),
            'end_time' => Carbon::now()->addMinutes(1),
            'session_token' => Str::random(20),
            'state' => 'COMPLETED',
            'correct_count' => 4,
            'wrong_count' => 6,
            'total_count' => 10,
            'points_gained' => 15,
            'created_at' => Carbon::today(),
            'updated_at' => Carbon::now()
        ]);

        $response = $this->postjson('/api/v2/game/start/single-player', [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2,
            "trivia" => $this->trivia->id
        ]);

        $response->assertJson([
            'message' => 'Attempt to play trivia twice',
        ]);
    }

    public function test_recent_live_trivia_can_be_fetched()
    {
        $response = $this->get(self::RECENT_LIVE_TRIVIA_URL);

        $response->assertStatus(200);
    }
}

<?php

namespace Tests\Feature;

use App\Enums\BonusType;
use App\Enums\FeatureFlags;
use App\Models\Bonus;
use App\Models\Category;
use App\Models\GameSession;
use App\Models\Plan;
use App\Models\Question;
use App\Models\Trivia;
use App\Models\User;
use App\Models\UserBonus;
use App\Models\UserPlan;
use App\Services\FeatureFlag;
use Carbon\Carbon;
use Database\Seeders\BonusSeeder;
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
        $this->seed(BonusSeeder::class);

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
        $response->assertOk();
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

        $response->assertOk();
    }

    public function test_exhibition_game_cannot_be_started_if_question_is_not_enough()
    {
        $questions = Question::factory()
            ->count(5)
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
            'expire_at' => Carbon::now()->endOfDay()
        ]);

        $response = $this->postjson(self::START_EXHIBITION_GAME_URL, [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2
        ]);
        $response->assertJson([
            'message' => 'Category not available for now, try again later',
        ]);
    }

    public function test_that_game_can_be_started_with_registration_bonus()
    {
        config(['features.registration_bonus.enabled' => true]);
        FeatureFlag::enable(FeatureFlags::EXHIBITION_GAME_STAKING);

        UserBonus::create([
            'user_id' => $this->user->id,
            'bonus_id' =>  Bonus::where('name', BonusType::RegistrationBonus->value)->first()->id,
            'is_on' => true,
            'amount_credited' => 500,
            'amount_remaining_after_staking' => 500,
            'total_amount_won'  => 0,
            'amount_remaining_after_withdrawal' => 0
        ]);

        $questions = Question::factory()
            ->count(50)
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

        $this->user->wallet->update([
            'non_withdrawable' => 2000,
            'bonus' => 500
        ]);

        $this->postjson('/api/v2/game/start/single-player', [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2,
            "staking_amount" => 200
        ]);

        $this->assertDatabaseHas('user_bonuses', [
            'user_id' => $this->user->id,
            'amount_remaining_after_staking' => 300
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $this->user->id,
            'bonus' => 300,
            'non_withdrawable' => 2000,
        ]);

    }

    public function test_that_game_cannot_be_started_with_registration_bonus_for_the_same_category_twice()
    {
        config(['features.registration_bonus.enabled' => true]);
        FeatureFlag::enable(FeatureFlags::EXHIBITION_GAME_STAKING);

        UserBonus::create([
            'user_id' => $this->user->id,
            'bonus_id' =>  Bonus::where('name', BonusType::RegistrationBonus->value)->first()->id,
            'is_on' => true,
            'amount_credited' => 500,
            'amount_remaining_after_staking' => 500,
            'total_amount_won'  => 0,
            'amount_remaining_after_withdrawal' => 0
        ]);

        GameSession::factory()
        ->create(['user_id' => $this->user->id, 'category_id' => $this->category->id]);

        $this->user->wallet->update([
            'non_withdrawable' => 2000,
            'bonus' => 500
        ]);

        $response = $this->postjson('/api/v2/game/start/single-player', [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2,
            "staking_amount" => 200
        ]);

        $response->assertJson([
            'message' => 'Sorry, you cannot play a category twice using your welcome bonus, Please play another.',
        ]);
    }

    public function test_that_game_cannot_be_started_if_staking_amount_is_less_than_registration_bonus()
    {
        config(['features.registration_bonus.enabled' => true]);
        config(['trivia.minimum_exhibition_staking_amount' => 400]);
        FeatureFlag::enable(FeatureFlags::EXHIBITION_GAME_STAKING);

        UserBonus::create([
            'user_id' => $this->user->id,
            'bonus_id' =>  Bonus::where('name', BonusType::RegistrationBonus->value)->first()->id,
            'is_on' => true,
            'amount_credited' => 1500,
            'amount_remaining_after_staking' => 500,
            'total_amount_won'  => 0,
            'amount_remaining_after_withdrawal' => 0
        ]);

        $this->user->wallet->update([
            'non_withdrawable' => 2000,
            'bonus' => 100
        ]);

        $response = $this->postjson('/api/v2/game/start/single-player', [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2,
            "staking_amount" => 600
        ]);

        $response->assertJson([
            'message' => 'Registration bonus is remaining 500 please stake 500',
        ]);
    }
}

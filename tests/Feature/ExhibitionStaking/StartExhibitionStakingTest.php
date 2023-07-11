<?php

namespace Tests\Feature\ExhibitionStaking;

use Tests\TestCase;
use App\Models\User;
use App\Models\Bonus;
use App\Enums\BonusType;
use App\Models\Question;
use App\Models\Category;
use App\Models\UserBonus;
use App\Models\GameSession;
use Database\Seeders\UserSeeder;
use Database\Seeders\BonusSeeder;
use Illuminate\Support\Facades\DB;
use Database\Seeders\CategorySeeder;
use Database\Seeders\GameModeSeeder;
use Database\Seeders\GameTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StartExhibitionStakingTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $category;
    const START_EXHIBITION_GAME_URL = '/api/v3/game/start/single-player';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        $this->seed(GameTypeSeeder::class);
        $this->seed(GameModeSeeder::class);
        $this->seed(BonusSeeder::class);

        $this->user = User::first();
        $this->category = Category::first();

        $this->actingAs($this->user);
    }


    public function test_that_game_can_be_started_with_registration_bonus()
    {
        config(['features.registration_bonus.enabled' => true]);

        UserBonus::create([
            'user_id' => $this->user->id,
            'bonus_id' => Bonus::where('name', BonusType::RegistrationBonus->value)->first()->id,
            'is_on' => true,
            'amount_credited' => 500,
            'amount_remaining_after_staking' => 500,
            'total_amount_won' => 0,
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

        $this->postjson(self::START_EXHIBITION_GAME_URL, [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2,
            "staking_amount" => 200,
            'wallet_type' => "bonus_balance"
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

    public function test_that_game_cannot_be_started_if_staking_amount_is_more_than_total_bonus_remaining()
    {
        config(['features.registration_bonus.enabled' => true]);
        config(['trivia.minimum_exhibition_staking_amount' => 400]);

        UserBonus::create([
            'user_id' => $this->user->id,
            'bonus_id' => Bonus::where('name', BonusType::RegistrationBonus->value)->first()->id,
            'is_on' => true,
            'amount_credited' => 1500,
            'amount_remaining_after_staking' => 500,
            'total_amount_won' => 0,
            'amount_remaining_after_withdrawal' => 0
        ]);

        $this->user->wallet->update([
            'non_withdrawable' => 2000,
            'bonus' => 600
        ]);

        $response = $this->postjson(self::START_EXHIBITION_GAME_URL, [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2,
            "staking_amount" => 800,
            'wallet_type' => "bonus_balance"
        ]);

        $response->assertJson([
            'message' => 'Insufficient bonus balance. Please contact support for help.',
        ]);
    }
    public function test_that_user_is_asked_to_stake_all_bonus_if_bonus_left_is_not_sufficient()
    {
        config(['trivia.minimum_exhibition_staking_amount' => 400]);

        $this->user->wallet->update([
            'non_withdrawable' => 2000,
            'bonus' => 1000
        ]);

        $response = $this->postjson(self::START_EXHIBITION_GAME_URL, [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2,
            "staking_amount" => 700,
            'wallet_type' => "bonus_balance"
        ]);

        $response->assertJson([
            'message' => 'Insufficient bonus amount will be left after this stake. Please stake 1000',
        ]);
    }
}
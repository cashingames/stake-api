<?php

namespace Tests\Feature;

use UserSeeder;
use BoostSeeder;
use CategorySeeder;
use GameModeSeeder;
use GameTypeSeeder;
use Tests\TestCase;
use App\Models\User;
use App\Models\Boost;
use App\Models\Bonus;
use App\Models\Option;
use App\Models\Staking;
use App\Enums\BonusType;
use App\Models\Category;
use App\Models\Question;
use App\Models\UserBoost;
use App\Models\UserBonus;
use App\Models\StakingOdd;
use App\Enums\FeatureFlags;
use App\Models\GameSession;
use App\Services\FeatureFlag;
use Illuminate\Support\Carbon;
use App\Models\ExhibitionStaking;
use Database\Seeders\BonusSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Database\Seeders\StakingOddSeeder;
use Database\Seeders\StakingOddsRulesSeeder;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GameTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * A basic feature test example.
     *
     * @return void
     */
    const COMMON_DATA_URL = '/api/v3/game/common';
    const START_EXHIBITION_GAME_URL = '/api/v3/game/start/single-player';
    const END_EXHIBITION_GAME_URL = '/api/v3/game/end/single-player';

    protected $user;
    protected $category;
    protected $staking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        $this->seed(BoostSeeder::class);
        $this->seed(GameTypeSeeder::class);
        $this->seed(GameModeSeeder::class);
        $this->seed(StakingOddSeeder::class);
        $this->seed(StakingOddsRulesSeeder::class);
        $this->seed(BonusSeeder::class);

        GameSession::factory()
            ->count(20)
            ->create();
        $this->user = User::first();
        $this->category = Category::where('category_id', '!=', 0)->inRandomOrder()->first();
        $this->actingAs($this->user);
        config(['odds.maximum_exhibition_staking_amount' => 1000]);
        config(['trivia.bonus.signup.stakers_bonus_amount' => 1000]);
    }

    public function test_common_data_can_be_retrieved()
    {
        $response = $this->get(self::COMMON_DATA_URL);

        $response->assertStatus(200);
    }

    public function test_common_data_can_be_retrieved_with_data()
    {
        $response = $this->get(self::COMMON_DATA_URL);

        $response->assertJsonStructure([
            'data' => [
                'boosts' => [],
                'gameModes' => [],
                'gameTypes' => [],
                'minVersionCode' => [],
                'minimumExhibitionStakeAmount' => [],
                'maximumExhibitionStakeAmount' => [],
                'minimumChallengeStakeAmount' => [],
                'maximumChallengeStakeAmount' => [],
            ]
        ]);
    }

    public function test_exhibition_game_can_be_ended_without_boosts_and_options()
    {   
        Staking::factory()->count(5)->create(['user_id' => $this->user->id]);
      
        GameSession::where('user_id', '!=', $this->user->id)->update(['user_id' => $this->user->id]);
        $game = $this->user->gameSessions()->first();
        $game->update(['state' => 'ONGOING']);

        ExhibitionStaking::factory()->create(['staking_id' => Staking::first()->id, 'game_session_id' => $game->id]);

        $response = $this->postjson(self::END_EXHIBITION_GAME_URL, [
            "token" => $game->session_token,
            "chosenOptions" => [],
            "consumedBoosts" => []
        ]);
        $response->assertJson([
            'message' => 'Game Ended',
        ]);
    }

    public function test_exhibition_game_can_be_started_with_staking()
    {
        $questions = Question::factory()
            ->hasOptions(4)
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

        $this->user->wallet->update([
            'non_withdrawable' => 5000
        ]);

        $response = $this->postjson(self::START_EXHIBITION_GAME_URL, [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2,
            "staking_amount" => 1000
        ]);

        /**
         * @TODO assert proper json structure
         */
        $response->assertOk();
    }

    public function test_that_exhibition_staking_record_is_created_in_exhibition_game_with_staking()
    {
        Staking::factory()->count(5)->create(['user_id' => $this->user->id]);
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

       
        $this->user->wallet->update([
            'non_withdrawable' => 1000
        ]);

        $this->postjson(self::START_EXHIBITION_GAME_URL, [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2,
            "staking_amount" => 1000
        ]);
        $this->assertDatabaseCount('exhibition_stakings', 1);
    }

    public function test_exhibition_staking_creates_a_winning_transaction_when_game_ends()
    {
        $questions = Question::factory()
            ->hasOptions(4)
            ->count(10)
            ->create();
        $chosenOptions = [];
        foreach ($questions as $question) {
            $chosenOptions[] = $question->options()->inRandomOrder()->first();
        }

        $this->user->wallet->update([
            'non_withdrawable' => 5000
        ]);

        GameSession::where('user_id', '!=', $this->user->id)->update(['user_id' => $this->user->id]);
        $game = $this->user->gameSessions()->first();
        $game->update(['state' => 'ONGOING']);

        $staking = Staking::create([
            'user_id' => $this->user->id,
            'amount_staked' => 1000
        ]);

        ExhibitionStaking::create([
            'staking_id' => $staking->id,
            'game_session_id' => $game->id
        ]);

        $this->postjson(self::END_EXHIBITION_GAME_URL, [
            "token" => $game->session_token,
            "chosenOptions" => $chosenOptions,
            "consumedBoosts" => []
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' =>  $this->user->wallet->id,
            'transaction_type' => 'CREDIT'
        ]);
        $correctOptionsCount = collect($chosenOptions)->filter(function ($value) {
            return $value->is_correct == 1;
        })->count();

        $expectedOdd = StakingOdd::where('score', $correctOptionsCount)->first()->odd;

        $this->assertDatabaseHas('exhibition_stakings', [
            'staking_id' => $staking->id,
            'game_session_id' => $game->id,
            'odds_applied' => $expectedOdd,
        ]);

        $this->assertDatabaseHas('stakings', [
            'id' => $staking->id,
            'amount_staked' => $staking->amount_staked,
            'amount_won' => $staking->amount_staked * $expectedOdd
        ]);
    }

    public function test_exhibition_staking_with_odd_awards_required_amount_with_odd()
    {

        $questions = Question::factory()
            ->hasOptions(4)
            ->count(10)
            ->create();
        $chosenOptions = [];
        foreach ($questions as $question) {
            $chosenOptions[] = $question->options()->inRandomOrder()->first();
        }

        $this->user->wallet->update([
            'non_withdrawable' => 5000
        ]);

        GameSession::where('user_id', '!=', $this->user->id)->update(['user_id' => $this->user->id]);
        $game = $this->user->gameSessions()->first();
        $game->update(['state' => 'ONGOING']);

        $staking = Staking::create([
            'user_id' => $this->user->id,
            'amount_staked' => 1000,
            'odd_applied_during_staking' => 3.0
        ]);

        ExhibitionStaking::create([
            'staking_id' => $staking->id,
            'game_session_id' => $game->id
        ]);

        $this->postjson(self::END_EXHIBITION_GAME_URL, [
            "token" => $game->session_token,
            "chosenOptions" => $chosenOptions,
            "consumedBoosts" => []
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' =>  $this->user->wallet->id,
            'transaction_type' => 'CREDIT'
        ]);
        $correctOptionsCount = collect($chosenOptions)->filter(function ($value) {
            return $value->is_correct == 1;
        })->count();

        $expectedOdd = StakingOdd::where('score', $correctOptionsCount)->first()->odd;
        $stakingOddApplied = $staking->odd_applied_during_staking;

        $this->assertDatabaseHas('exhibition_stakings', [
            'staking_id' => $staking->id,
            'game_session_id' => $game->id,
            'odds_applied' => $expectedOdd *  $stakingOddApplied,
        ]);

        $this->assertDatabaseHas('stakings', [
            'id' => $staking->id,
            'amount_staked' => $staking->amount_staked,
            'amount_won' => $staking->amount_staked * $expectedOdd *  $stakingOddApplied
        ]);
    }


    public function test_bonus_balance_is_being_deducted_for_staking_when_a_user_has_bonus()
    {
        Staking::factory()->count(5)->create(['user_id' => $this->user->id]);
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

        FeatureFlag::enable(FeatureFlags::REGISTRATION_BONUS);
        $this->user->wallet->update([
            'non_withdrawable' => 2000,
            'bonus' => 1000
        ]);

        $this->postjson(self::START_EXHIBITION_GAME_URL, [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2,
            "staking_amount" => 500
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $this->user->id,
            'bonus' => 500,
            'non_withdrawable' => 2000
        ]);
    }

    public function test_message_is_shown_if_staking_amount_is_less_than_bonus()
    {
        Staking::factory()->count(5)->create(['user_id' => $this->user->id]);
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

        FeatureFlag::enable(FeatureFlags::REGISTRATION_BONUS);
        $this->user->wallet->update([
            'non_withdrawable' => 2000,
            'bonus' => 100
        ]);

        $response = $this->postjson(self::START_EXHIBITION_GAME_URL, [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2,
            "staking_amount" => 500
        ]);

        $response->assertJson([
            'message' => 'Insufficient bonus balance. Please exhaust your bonuses to proceed',
        ]);
    }

    public function test_bonus_amount_is_credited_for_user_with_registration_bonus_when_game_ends()
    {
        FeatureFlag::enable(FeatureFlags::REGISTRATION_BONUS);
        config(['trivia.user_scores.perfect_score' => 10]);

        Staking::factory()->create(['user_id' => $this->user->id]);
        UserBonus::create([
            'user_id' => $this->user->id,
            'bonus_id' =>  Bonus::where('name', BonusType::RegistrationBonus->value)->first()->id,
            'is_on' => true,
            'amount_credited' => 1500,
            'amount_remaining_after_staking' => 500,
            'total_amount_won'  => 0,
            'amount_remaining_after_withdrawal' => 0
        ]);
        GameSession::where('user_id', '!=', $this->user->id)
            ->update(['user_id' => $this->user->id, 'correct_count' => 10]);

        ExhibitionStaking::factory()
            ->create(['game_session_id' => GameSession::first()->id, 'staking_id' => Staking::first()->id]);

        $game = $this->user->gameSessions()->first();
        $game->update(['state' => 'ONGOING']);

        $questions = Question::factory()
            ->hasOptions(4)
            ->count(10)
            ->create();

        Option::query()->update(['is_correct' => true]);

        $chosenOptions = [];
        foreach ($questions as $question) {

            $chosenOptions[] = $question->options()->inRandomOrder()->first();
        }

        $this->postjson(self::END_EXHIBITION_GAME_URL, [
            "token" => $game->session_token,
            "chosenOptions" => $chosenOptions,
            "consumedBoosts" => []
        ]);

        $userBonus = UserBonus::where('user_id', $this->user->id)
            ->where('bonus_id', Bonus::where('name', BonusType::RegistrationBonus->value)->first()->id)
            ->first();

        $this->assertEquals(
            $this->user->wallet->withdrawable,
            $userBonus->total_amount_won + $userBonus->amount_credited
        );
    }
}

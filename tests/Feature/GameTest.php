<?php

namespace Tests\Feature;

use PlanSeeder;
use UserSeeder;
use BoostSeeder;
use CategorySeeder;
use GameModeSeeder;
use GameTypeSeeder;
use Tests\TestCase;
use App\Models\Plan;
use App\Models\User;
use App\Models\Boost;
use App\Models\Category;
use App\Models\Question;
use App\Models\UserPlan;
use App\Models\UserBoost;
use App\Models\GameSession;
use App\Models\UserCoin;
use Database\Seeders\AchievementBadgeSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

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
    protected $plan;
    protected $staking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        $this->seed(BoostSeeder::class);
        $this->seed(GameTypeSeeder::class);
        $this->seed(GameModeSeeder::class);
        $this->seed(PlanSeeder::class);
        $this->seed(AchievementBadgeSeeder::class);
        GameSession::factory()
            ->count(20)
            ->create();
        $this->user = User::first();
        $this->category = Category::where('category_id', '!=', 0)->inRandomOrder()->first();
        $this->plan = Plan::inRandomOrder()->first();
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
                'plans' => [],
                'gameModes' => [],
                'gameTypes' => [],
                'minVersionCode' => [],
                'minimumExhibitionStakeAmount' => [],
                'maximumExhibitionStakeAmount' => [],
            ]
        ]);
    }

    public function test_exhibition_game_can_be_started()
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

    public function test_exhibition_game_can_be_ended_without_boosts_and_options()
    {
        GameSession::where('user_id', '!=', $this->user->id)->update(['user_id' => $this->user->id]);
        $game = $this->user->gameSessions()->first();
        $game->update(['state' => 'ONGOING']);

        $response = $this->postjson(self::END_EXHIBITION_GAME_URL, [
            "token" => $game->session_token,
            "chosenOptions" => [],
            "consumedBoosts" => []
        ]);
        $response->assertJson([
            'message' => 'Game Ended',
        ]);
    }

    public function test_gameark_exhibition_game_can_be_ended_with_boosts_and_no_options()
    {
        GameSession::where('user_id', '!=', $this->user->id)->update(['user_id' => $this->user->id]);
        $game = $this->user->gameSessions()->first();
        $game->update(['state' => 'ONGOING']);

        $boost = Boost::inRandomOrder()->first();

        UserBoost::create([
            'user_id' => $this->user->id,
            'boost_id' => $boost->id,
            'boost_count' => $boost->pack_count,
            'used_count' => 0
        ]);

        $userBoost = $this->user->gameArkUserBoosts();

        $response = $this->postjson(self::END_EXHIBITION_GAME_URL, [
            "token" => $game->session_token,
            "chosenOptions" => [],
            "consumedBoosts" => [
                ['boost' => Boost::where('id', $userBoost[0]->id)->first()]
            ]
        ]);


        $response->assertJson([
            'message' => 'Game Ended',
        ]);
    }

    public function test_used_boost_is_saved_when_exhibition_game_ends()
    {
        GameSession::where('user_id', '!=', $this->user->id)->update(['user_id' => $this->user->id]);
        $game = $this->user->gameSessions()->first();
        $game->update(['state' => 'ONGOING']);

        $boost = Boost::inRandomOrder()->first();

        UserBoost::create([
            'user_id' => $this->user->id,
            'boost_id' => $boost->id,
            'boost_count' => $boost->pack_count,
            'used_count' => 0
        ]);

        $userBoost = $this->user->gameArkUserBoosts();

        $this->postjson(self::END_EXHIBITION_GAME_URL, [
            "token" => $game->session_token,
            "chosenOptions" => [],
            "consumedBoosts" => [
                ['boost' => Boost::where('id', $userBoost[0]->id)->first()]
            ]
        ]);

        $this->assertDatabaseHas('exhibition_boosts', [
            'boost_id' => Boost::where('id', $userBoost[0]->id)->first()->id,
            'game_session_id' => $game->id,
        ]);

    }

    public function test_standard_exhibition_game_cannot_be_started_if_no_plan()
    {
        $response = $this->postjson(self::START_EXHIBITION_GAME_URL, [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2
        ]);
        $response->assertJson([
            'message' => 'You do not have a valid plan',
        ]);
    }

    public function test_that_referral_can_be_rewarded_after_referee_ends_first_game()
    {
        // create new user
        $newUser = User::create([
            'username' => 'testUser',
            'phone_number' => '08133445858',
            'email' => 'testaccount@gmail.com',
            'password' => 'xcvb',
            'otp_token' => '2134',
            'is_on_line' => true,
            'country_code' => 'NGN'
        ]);
        $newUser
            ->profile()
            ->create([
                'first_name' => 'zxcv',
                'last_name' => 'asdasd',
                'referral_code' => 'zxczxc',
                'referrer' => $this->user->profile->referrer,
            ]);

        $newUser->wallet()
            ->create([]);

        DB::table('user_plans')->insert([
            'user_id' => $newUser->id,
            'plan_id' => 1,
            'description' => "Registration Daily bonus plan for " . $newUser->username,
            'is_active' => true,
            'used_count' => 0,
            'plan_count' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'expire_at' => Carbon::now()->endOfDay()
        ]);

        // play game and end
        #start
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
        $this->postjson(self::START_EXHIBITION_GAME_URL, [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2
        ]);

        #end game
        GameSession::factory()
            ->count(20)
            ->create();
        GameSession::where('user_id', '!=', $this->user->id)->update(['user_id' => $this->user->id]);
        $game = $this->user->gameSessions()->first();
        $game->update(['state' => 'ONGOING', 'user_id' => $newUser->id]);

        $this->postjson(self::END_EXHIBITION_GAME_URL, [
            "token" => $game->session_token,
            "chosenOptions" => [],
            "consumedBoosts" => []
        ]);

        # check if referrer has a new gifted game
        $this->assertDatabaseHas('user_plans', [
            'user_id' => $newUser->id,
            'plan_id' => 1
        ]);

    }

    public function test_gameark_game_options_is_recieved_with_approach_answers()
    {
        $questions = Question::factory()
            ->count(250)
            ->hasOptions(4)
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

        $this->withHeaders([
            'x-brand-id' => 10,
        ]);

        $response = $this->postjson(self::START_EXHIBITION_GAME_URL, [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2
        ]);

        $response->assertJsonStructure([
            "message",
            "data" => [
                "questions" => [
                    [
                        "options" => [
                            [
                                "is_correct"
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function test_gameark_users_can_be_rewarded_coins_after_game_ended()
    {
        GameSession::where('user_id', '!=', $this->user->id)->update(['user_id' => $this->user->id]);
        $game = $this->user->gameSessions()->first();
        $game->update(['state' => 'ONGOING']);

        $response = $this->withHeaders([
            'x-brand-id' => 10,
        ])->postjson(self::END_EXHIBITION_GAME_URL, [
            "token" => $game->session_token,
            "chosenOptions" => [],
            "consumedBoosts" => []
        ]);
        $response->assertJsonStructure([
            "message",
            "data" => [
                "coins_earned"
            ]
        ]);
    }

    public function test_that_an_old_user_coin_is_updated_after_gameplay()
    {
        Config::set('trivia.coin_reward.user_scores.perfect_score', 10);
        Config::set('trivia.coin_reward.coins_earned.perfect_coin', 30);
        
        GameSession::where('user_id', '!=', $this->user->id)->update(['user_id' => $this->user->id]);
        $game = $this->user->gameSessions()->first();
        $game->update(['state' => 'ONGOING']);
        $response = $this->withHeaders([
            'x-brand-id' => 10,
        ])->postjson(self::END_EXHIBITION_GAME_URL, [
            "token" => $game->session_token,
            "chosenOptions" => [],
            "consumedBoosts" => []
        ]);
        UserCoin::create(['user_id' => $this->user->id, 'coins_value' => 10]);
        $coinsWon = $this->user->getUserCoins() + config('trivia.coin_reward.coins_earned.perfect_coin');
        UserCoin::where('user_id', $this->user->id)->update(['coins_value' => $coinsWon ]);
        $game->update(['correct_count' => config('trivia.coin_reward.coins_earned.perfect_score')]);
        $this->assertDatabaseHas('user_coins', [
            'user_id' => $this->user->id,
            'coins_value' => $coinsWon
        ]);
    }
    
    public function test_that_userCoin_transaction_is_created_after_user_awarded_with_coins()
    {
        Config::set('trivia.coin_reward.user_scores.perfect_score', 10);
        Config::set('trivia.coin_reward.coins_earned.perfect_coin', 30);
        
        GameSession::where('user_id', '!=', $this->user->id)->update(['user_id' => $this->user->id]);
        $game = $this->user->gameSessions()->first();
        $game->update(['state' => 'ONGOING']);
        $response = $this->withHeaders([
            'x-brand-id' => 10,
        ])->postjson(self::END_EXHIBITION_GAME_URL, [
            "token" => $game->session_token,
            "chosenOptions" => [],
            "consumedBoosts" => []
        ]);

        $this->assertDatabaseHas('user_coin_transactions', [
            'user_id' => $this->user->id,
            'transaction_type' => 'CREDIT',
            'description' => 'Game coins awarded',
            'value' => 0
        ]);
    }

    public function test_that_a_new_user_coin_is_awarded_coin_after_play()
    {

        Config::set('trivia.coin_reward.user_scores.perfect_score', 10);
        Config::set('trivia.coin_reward.coins_earned.perfect_coin', 30);
         // create new user
         $newUser = User::create([
            'username' => 'testUser',
            'phone_number' => '08133445858',
            'email' => 'testaccount@gmail.com',
            'password' => 'xcvb',
            'otp_token' => '2134',
            'is_on_line' => true,
            'country_code' => 'NGN'
        ]);
        $newUser
            ->profile()
            ->create([
                'first_name' => 'zxcv',
                'last_name' => 'asdasd',
                'referral_code' => 'zxczxc',
                'referrer' => $this->user->profile->referrer,
            ]);

        $newUser->wallet()
            ->create([]);

            $this->actingAs($newUser);

        DB::table('user_plans')->insert([
            'user_id' => $newUser->id,
            'plan_id' => 1,
            'description' => "Registration Daily bonus plan for " . $newUser->username,
            'is_active' => true,
            'used_count' => 0,
            'plan_count' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'expire_at' => Carbon::now()->endOfDay()
        ]);

        // play game and end
        #start
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
        $this->postjson(self::START_EXHIBITION_GAME_URL, [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2
        ]);

        #end game
        GameSession::factory()
            ->count(20)
            ->create();
        GameSession::where('user_id', '!=', $this->user->id)->update(['user_id' => $newUser->id]);
        $game = $newUser->gameSessions()->first();
        $game->update(['state' => 'ONGOING']);

        $response = $this->withHeaders([
            'x-brand-id' => 10,
        ])->postjson(self::END_EXHIBITION_GAME_URL, [
            "token" => $game->session_token,
            "chosenOptions" => [],
            "consumedBoosts" => []
        ]);

        $coinsWon = $newUser->getUserCoins();    
        $this->assertDatabaseHas('user_coins', [
            'user_id' => $newUser->id,
            'coins_value' => $coinsWon
        ]);
    }



}

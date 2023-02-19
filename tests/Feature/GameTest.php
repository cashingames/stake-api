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
use AchievementSeeder;
use App\Enums\FeatureFlags;
use App\Mail\ChallengeInvite;
use App\Models\Category;
use App\Models\Question;
use App\Models\UserPlan;
use App\Models\UserBoost;
use App\Models\UserPoint;
use App\Models\Achievement;
use App\Models\ExhibitionStaking;
use App\Models\GameSession;
use App\Models\Option;
use App\Models\Staking;
use App\Models\StakingOdd;
use App\Notifications\ChallengeReceivedNotification;
use App\Services\FeatureFlag;
use Database\Seeders\StakingOddSeeder;
use Database\Seeders\StakingOddsRulesSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class GameTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    const COMMON_DATA_URL = '/api/v3/game/common';
    const CLAIM_ACHIEVEMENT_URL = '/api/v3/claim/achievement/';
    const START_EXHIBITION_GAME_URL = '/api/v3/game/start/single-player';
    const END_EXHIBITION_GAME_URL = '/api/v3/game/end/single-player';
    const SEND_CHALLENGE_INVITE_URL = "/api/v3/challenge/send-invite";
    const END_CHALLENGE_URL = "/api/v3/challenge/end/game";

    protected $user;
    protected $category;
    protected $plan;
    protected $staking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        $this->seed(AchievementSeeder::class);
        $this->seed(BoostSeeder::class);
        $this->seed(GameTypeSeeder::class);
        $this->seed(GameModeSeeder::class);
        $this->seed(PlanSeeder::class);
        $this->seed(StakingOddSeeder::class);
        $this->seed(StakingOddsRulesSeeder::class);
        GameSession::factory()
            ->count(20)
            ->create();
        $this->user = User::first();
        $this->category = Category::where('category_id', '!=', 0)->inRandomOrder()->first();
        $this->plan = Plan::inRandomOrder()->first();
        $this->actingAs($this->user);
        FeatureFlag::isEnabled(FeatureFlags::EXHIBITION_GAME_STAKING);
        FeatureFlag::isEnabled(FeatureFlags::TRIVIA_GAME_STAKING);
        config(['odds.maximum_exhibition_staking_amount' => 1000]);
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
                'achievements' => [],
                'boosts' => [],
                'plans' => [],
                'gameModes' => [],
                'gameTypes' => [],
                'minVersionCode' => [],
                'minimumExhibitionStakeAmount' => [],
                'maximumExhibitionStakeAmount' => [],
                'minimumChallengeStakeAmount' => [],
                'maximumChallengeStakeAmount' => [],
                'minimumLiveTriviaStakeAmount' => [],
                'maximumLiveTriviaStakeAmount' => [],
            ]
        ]);
    }

    public function test_achievement_can_be_claimed()
    {
        $achievement = Achievement::first();

        UserPoint::create([
            'user_id' => $this->user->id,
            'value' => 5000,
            'description' => 'Test points added',
            'point_flow_type' => 'POINTS_ADDED'
        ]);

        $response = $this->post(self::CLAIM_ACHIEVEMENT_URL . $achievement->id);

        $response->assertStatus(200);
    }

    public function test_achievement_cannot_be_claimed_if_points_are_not_enough()
    {
        $achievement = Achievement::first();

        $response = $this->post(self::CLAIM_ACHIEVEMENT_URL . $achievement->id);

        $response->assertJson([
            'errors' => 'You do not have enough points to claim this achievement',
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
        $response->assertJson([
            'message' => 'Game Started',
        ]);
    }

    public function test_exhibition_game_can_be_ended_without_boosts_and_options()
    {
        FeatureFlag::enable(FeatureFlags::EXHIBITION_GAME_STAKING);
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

    public function test_exhibition_game_can_be_ended_with_boosts_and_no_options()
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

        $userBoost = $this->user->userBoosts();

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

        $userBoost = $this->user->userBoosts();

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

    public function test_challenge_invite_sent_successfully()
    {
        Mail::fake();
        Notification::fake();

        $player = $this->user;
        $opponent = User::latest()->limit(2)->first();
        $category = $this->category;

        $questions = Question::factory()
        ->count(10)
        ->create();

        foreach ($questions as $question) {
            $data[] = [
                'category_id' => $this->category->id,
                'question_id' => $question->id
            ];
        }

        DB::table('categories_questions')->insert($data);

        $response = $this->postJson(self::SEND_CHALLENGE_INVITE_URL, [
            'opponentId' => $opponent->id,
            'categoryId' => $category->id
        ]);

        $this->assertDatabaseHas('challenges', [
            'category_id' => $category->id,
            'user_id' => $player->id,
            'opponent_id' => $opponent->id
        ]);
        Mail::assertQueued(ChallengeInvite::class);
        Notification::assertSentTo($opponent, ChallengeReceivedNotification::class);
        $response->assertOk();
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
        $this->user->wallet->update([
            'non_withdrawable_balance' => 5000
        ]);

        $response = $this->postjson(self::START_EXHIBITION_GAME_URL, [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2,
            "staking_amount" => 500
        ]);
        $response->assertJson([
            'message' => 'Game Started',
        ]);
    }



    public function test_that_a_staking_exhibition_game_does_not_start_if_wallet_balance_is_insufficient()
    {

        $response = $this->postjson(self::START_EXHIBITION_GAME_URL, [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2,
            "staking_amount" => 500
        ]);

        // {"message":"Insufficient funds","errors":{"staking_amount":["Insufficient funds"]}}
        $response->assertJson([
            'message' => 'Insufficient funds',
            'errors' => [
                'staking_amount' => ['Insufficient funds']
            ]
        ]);
    }

    public function test_that_exhibition_staking_record_is_created_in_exhibition_game_with_staking()
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

        FeatureFlag::enable(FeatureFlags::EXHIBITION_GAME_STAKING);
        $this->user->wallet->update([
            'non_withdrawable_balance' => 1000
        ]);
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
        $this->postjson('/api/v2/game/start/single-player', [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2,
            "staking_amount" => 500
        ]);
        $this->assertDatabaseCount('exhibition_stakings', 1);
    }
    public function test_exhibition_game_with_staking_does_not_require_game_lives()
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

        UserPlan::create([
            'plan_id' => $this->plan->id,
            'user_id' => $this->user->id,
            'used_count' => $this->plan->game_count,
            'plan_count' => 1,
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'expire_at' => Carbon::now()->endOfDay()
        ]);

        $this->user->wallet->update([
            'non_withdrawable_balance' => 5000
        ]);

        $response = $this->postjson(self::START_EXHIBITION_GAME_URL, [
            "category" => $this->category->id,
            "mode" => 1,
            "type" => 2,
            "staking_amount" => 500
        ]);
        $response->assertJson([
            'message' => 'Game Started',
        ]);
    }

    public function test_exhibition_staking_creates_a_winning_transaction_when_game_ends()
    {
        FeatureFlag::enable('odds');
        FeatureFlag::enable(FeatureFlags::EXHIBITION_GAME_STAKING);
        $questions = Question::factory()
            ->hasOptions(4)
            ->count(10)
            ->create();
        $chosenOptions = [];
        foreach ($questions as $question) {
            $chosenOptions[] = $question->options()->inRandomOrder()->first();
        }

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
        $this->user->wallet->update([
            'non_withdrawable_balance' => 5000
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
            return base64_decode($value->is_correct) == 1;
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
        FeatureFlag::enable('odds');
        FeatureFlag::enable(FeatureFlags::EXHIBITION_GAME_STAKING);
        FeatureFlag::enable(FeatureFlags::STAKING_WITH_ODDS);

        $questions = Question::factory()
            ->hasOptions(4)
            ->count(10)
            ->create();
        $chosenOptions = [];
        foreach ($questions as $question) {
            $chosenOptions[] = $question->options()->inRandomOrder()->first();
        }

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
        $this->user->wallet->update([
            'non_withdrawable_balance' => 5000
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
            return base64_decode($value->is_correct) == 1;
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
}

<?php

namespace Tests\Feature\ExhibitionStaking;

use App\Enums\BonusType;
use App\Jobs\FillCashdropPools;
use App\Models\Bonus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Category;
use App\Models\GameSession;
use App\Models\Staking;
use App\Models\User;
use App\Models\ExhibitionStaking;
use App\Models\Option;
use App\Models\Question;
use App\Models\StakingOdd;
use UserSeeder;
use BoostSeeder;
use CategorySeeder;
use BonusSeeder;
use Illuminate\Support\Facades\Queue;
use StakingOddSeeder;
use StakingOddsRulesSeeder;
use App\Jobs\SendAdminErrorEmailUpdate;


class EndSinglePlayerGameTest extends TestCase
{
    use RefreshDatabase;
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

    public function test_exhibition_game_can_be_ended_without_boosts_and_options()
    {   
        Queue::fake();
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
          //assert that cash drop filling job was queued
          Queue::assertPushed(FillCashdropPools::class);
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

    public function test_user_is_credited_with_highest_bonus_odd_when_user_scores_perfect_with_registration_bonus()
    {
        config(['bonusOdds' => [
            [
                'id' => 1,
                'score' => 10,
                'odd' => 5
            ],
        ]]);

        $staking = Staking::factory()->create(['user_id' => $this->user->id, 'fund_source' => 'BONUS_BALANCE']);

        ExhibitionStaking::factory()
            ->create(['game_session_id' => GameSession::first()->id, 'staking_id' => Staking::first()->id]);

        GameSession::where('user_id', '!=', $this->user->id)
            ->update(['user_id' => $this->user->id, 'correct_count' => 10]);

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

        $this->assertEquals(
            $this->user->wallet->withdrawable,
            config('bonusOdds')[0]['odd'] * $staking->amount_staked
        );
    }

    public function test_user_is_credited_with_zero_amount_when_user_does_not_score_perfect_with_registration_bonus()
    {
        config(['bonusOdds' => [
            [
                'id' => 1,
                'score' => 10,
                'odd' => 5
            ],
            [
                'id' => 2,
                'score' => 0,
                'odd' => 0
            ],

        ]]);

        Staking::factory()->create(['user_id' => $this->user->id, 'fund_source' => 'BONUS_BALANCE']);
     
        ExhibitionStaking::factory()
            ->create(['game_session_id' => GameSession::first()->id, 'staking_id' => Staking::first()->id]);

        GameSession::where('user_id', '!=', $this->user->id)
            ->update(['user_id' => $this->user->id, 'correct_count' => 10]);

        $game = $this->user->gameSessions()->first();
        $game->update(['state' => 'ONGOING']);

        Question::factory()
            ->hasOptions(4)
            ->count(10)
            ->create();

        $this->postjson(self::END_EXHIBITION_GAME_URL, [
            "token" => $game->session_token,
            "chosenOptions" => [],
            "consumedBoosts" => []
        ]);

        $this->assertEquals(
            $this->user->wallet->withdrawable,
            0
        );
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

    public function test_admin_email_is_dispatched_when_invalid_token_is_sent()
    {
        Staking::factory()->count(5)->create(['user_id' => $this->user->id]);

        GameSession::where('user_id', '!=', $this->user->id)->update(['user_id' => $this->user->id]);
        $game = $this->user->gameSessions()->first();
        $game->update(['state' => 'ONGOING']);

        ExhibitionStaking::factory()->create(['staking_id' => Staking::first()->id, 'game_session_id' => $game->id]);
        Queue::fake();

        $response = $this->postjson(self::END_EXHIBITION_GAME_URL, [
            "token" => "invalidToken123456789",
            "chosenOptions" => [],
            "consumedBoosts" => []
        ]);

        Queue::assertPushed(SendAdminErrorEmailUpdate::class);

        $response->assertJson([
            'message' => 'Game Session does not exist',
        ]);
    }

    public function test_admin_email_is_dispatched_when_game_is_ended_twice()
    {
        Staking::factory()->count(5)->create(['user_id' => $this->user->id]);

        GameSession::where('user_id', '!=', $this->user->id)->update(['user_id' => $this->user->id]);
        $game = $this->user->gameSessions()->first();
        $game->update(['state' => 'COMPLETED']);

        ExhibitionStaking::factory()->create(['staking_id' => Staking::first()->id, 'game_session_id' => $game->id]);
        Queue::fake();

        $this->postjson(self::END_EXHIBITION_GAME_URL, [
            "token" => "invalidToken123456789",
            "chosenOptions" => [],
            "consumedBoosts" => []
        ]);

        Queue::assertPushed(SendAdminErrorEmailUpdate::class);
    }

    public function test_admin_email_is_dispatched_when_more_than_expected_options_are_submitted()
    {
        $questions = Question::factory()
            ->hasOptions(4)
            ->count(15)
            ->create();
        $chosenOptions = [];
        foreach ($questions as $question) {
            $chosenOptions[] = $question->options()->inRandomOrder()->first();
        }
        Staking::factory()->count(5)->create(['user_id' => $this->user->id]);

        GameSession::where('user_id', '!=', $this->user->id)->update(['user_id' => $this->user->id]);
        $game = $this->user->gameSessions()->first();
        $game->update(['state' => 'ONGOING']);

        ExhibitionStaking::factory()->create(['staking_id' => Staking::first()->id, 'game_session_id' => $game->id]);
        Queue::fake();

        $this->postjson(self::END_EXHIBITION_GAME_URL, [
            "token" => $game->session_token,
            "chosenOptions" =>  $chosenOptions,
            "consumedBoosts" => []
        ]);

        Queue::assertPushed(SendAdminErrorEmailUpdate::class);
    }
}

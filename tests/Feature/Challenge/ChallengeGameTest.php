<?php

namespace Tests\Feature\Challenge;

use Tests\TestCase;
use App\Models\User;
use App\Models\Staking;
use App\Models\Category;
use App\Models\Question;
use App\Models\Challenge;
use Mockery\MockInterface;
use App\Enums\FeatureFlags;
use Illuminate\Support\Str;
use App\Mail\ChallengeInvite;
use App\Services\FeatureFlag;
use App\Services\StakingService;
use Database\Seeders\UserSeeder;
use App\Models\ChallengeGameSession;
use Database\Seeders\CategorySeeder;
use Database\Seeders\GameModeSeeder;
use Database\Seeders\AchievementBadgeSeeder;
use Database\Seeders\GameTypeSeeder;
use Illuminate\Support\Facades\Mail;
use App\Actions\SendPushNotification;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Notifications\ChallengeReceivedNotification;
use App\Notifications\ChallengeCompletedNotification;
use App\Services\ChallengeGameService;
use Illuminate\Support\Facades\DB;

class ChallengeGameTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    const SEND_CHALLENGE_INVITE_URL = "/api/v3/challenge/send-invite";
    const START_CHALLENGE_GAME_URL = "/api/v3/challenge/start/game";
    const END_CHALLENGE_URL = "/api/v3/challenge/end/game";
    const CHALLENGE_RESPONSE_URL = "/api/v3/challenge/invite/respond";

    protected $user;
    protected $category;

    protected $staking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);


        $this->seed(GameTypeSeeder::class);
        $this->seed(GameModeSeeder::class);
        $this->seed(AchievementBadgeSeeder::class);

        $this->user = User::first();
        $this->category = Category::where('category_id', '!=', 0)->inRandomOrder()->first();

        $this->actingAs($this->user);
    }


    public function test_challenge_invite_sent_with_staking_successfully()
    {
        FeatureFlag::enable(FeatureFlags::CHALLENGE_GAME_STAKING);
        Mail::fake();
        Notification::fake();


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

        $this->user->wallet()->update([
            'non_withdrawable' => 2500,
        ]);

        $player = $this->user;
        $opponent = User::where('id', '<>', $player->id)->first();


        $category = $this->category;
        $amountToStake = 1500;

        $response = $this->postJson(self::SEND_CHALLENGE_INVITE_URL, [
            'opponentId' => $opponent->id,
            'categoryId' => $category->id,
            'staking_amount' => $amountToStake
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('challenges', [
            'category_id' => $category->id,
            'user_id' => $player->id,
            'opponent_id' => $opponent->id
        ]);

        $this->assertDatabaseHas('stakings', [
            'amount_staked' => $amountToStake,
            'user_id' => $player->id
        ]);

        $staking = Staking::first();

        $challenge = Challenge::where('user_id', $player->id)
            ->where('opponent_id', $opponent->id)
            ->where('category_id', $category->id)
            ->first();

        $this->assertDatabaseHas('challenge_stakings', [
            'challenge_id' => $challenge->id,
            'staking_id' => $staking->id
        ]);
        Mail::assertSent(ChallengeInvite::class);
        Notification::assertSentTo($opponent, ChallengeReceivedNotification::class);
    }

    public function test_can_not_accept_staking_challenge_with_insufficient_balance()
    {
        FeatureFlag::enable(FeatureFlags::CHALLENGE_GAME_STAKING);
        $opponent = $this->user;
        $creator = User::where('id', '<>', $opponent->id)->first();
        $category = $this->category;

        $amountToStake = 1500;

        $challenge = Challenge::create([
            'user_id' => $creator->id,
            'opponent_id' => $opponent->id,
            'category_id' => $category->id,
            'status' => 'PENDING'
        ]);

        $stakingService = new StakingService($creator);
        $stakingId = $stakingService->stakeAmount($amountToStake);
        $stakingService->createChallengeStaking($stakingId, $challenge->id);
        $acceptanceResponse = $this->postJson(self::CHALLENGE_RESPONSE_URL, [
            'status' => true,
            'challenge_id' => $challenge->id
        ])->assertStatus(400);
        $acceptanceResponse->assertJson([
            "message" => "You do not have enough balance to accept this challenge"
        ]);
    }

    public function test_can_not_accept_invalid_challenge()
    {
        $acceptanceResponse = $this->postJson(self::CHALLENGE_RESPONSE_URL, [
            'status' => true,
        ])->assertStatus(400);
    }
    public function test_can_accept_challenge_with_staking()
    {
        FeatureFlag::enable(FeatureFlags::CHALLENGE_GAME_STAKING);

        $this->user->wallet()->update([
            'non_withdrawable' => 2500,
        ]);

        $opponent = $this->user;
        $creator = User::where('id', '<>', $opponent->id)->first();
        $category = $this->category;

        $amountToStake = 1500;

        $challenge = Challenge::create([
            'user_id' => $creator->id,
            'opponent_id' => $opponent->id,
            'category_id' => $category->id,
            'status' => 'PENDING'
        ]);

        $stakingService = new StakingService($creator);
        $stakingId = $stakingService->stakeAmount($amountToStake);
        $stakingService->createChallengeStaking($stakingId, $challenge->id);

        $acceptanceResponse = $this->postJson(self::CHALLENGE_RESPONSE_URL, [
            'status' => true,
            'challenge_id' => $challenge->id
        ]);

        $acceptanceResponse->assertOk();

        $this->assertDatabaseHas('stakings', [
            'amount_staked' => $amountToStake,
            'user_id' => $opponent->id
        ]);

        $staking = Staking::where('user_id', $opponent->id)->first();

        $this->assertDatabaseHas('challenge_stakings', [
            'challenge_id' => $challenge->id,
            'staking_id' => $staking->id
        ]);
    }

    public function test_challenge_game_ends_for_first_player_successfully()
    {
        Notification::fake();
        $this->mock(SendPushNotification::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendChallengeCompletedNotification')->once();
        });
        $questions = Question::factory()
            ->hasOptions(4)
            ->count(10)
            ->create();
        $chosenOptions = [];
        foreach ($questions as $question) {
            $chosenOptions[] = $question->options()->inRandomOrder()->first();
        }

        $creator = $this->user;
        $opponent = User::where('id', '<>', $this->user->id)->first();
        $category = $this->category;

        $challenge = Challenge::create([
            'user_id' => $creator->id,
            'opponent_id' => $opponent->id,
            'category_id' => $category->id,
            'status' => 'PENDING'
        ]);

        $startGameResponse = $this->postJson(self::START_CHALLENGE_GAME_URL, [
            'challenge_id' => $challenge->id,
            'category' => $category->id,
            'type' => 2
        ])->assertOk();


        $gameSession = $this->user->challengeGameSessions()->first();


        $this->postjson(self::END_CHALLENGE_URL, [
            "token" => $gameSession->session_token,
            "chosenOptions" => $chosenOptions,
            "consumedBoosts" => []
        ]);

        Notification::assertSentTo($opponent, ChallengeCompletedNotification::class);
    }

    public function test_challenge_game_ends_for_first_player_successfully_on_gameark()
    {
        Notification::fake();
        $this->mock(SendPushNotification::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendChallengeCompletedNotification')->once();
        });
        $questions = Question::factory()
            ->hasOptions(4)
            ->count(10)
            ->create();
        $chosenOptions = [];
        foreach ($questions as $question) {
            $chosenOptions[] = $question->options()->inRandomOrder()->first();
        }

        $creator = $this->user;
        $opponent = User::where('id', '<>', $this->user->id)->first();
        $category = $this->category;

        $challenge = Challenge::create([
            'user_id' => $creator->id,
            'opponent_id' => $opponent->id,
            'category_id' => $category->id,
            'status' => 'PENDING'
        ]);

        $this->withHeaders([
            'x-brand-id' => 10,
        ]);

        $startGameResponse = $this->postJson(self::START_CHALLENGE_GAME_URL, [
            'challenge_id' => $challenge->id,
            'category' => $category->id,
            'type' => 2
        ])->assertOk();

        $gameSession = $this->user->challengeGameSessions()->first();


        $this->postjson(self::END_CHALLENGE_URL, [
            "token" => $gameSession->session_token,
            "chosenOptions" => $chosenOptions,
            "consumedBoosts" => []
        ]);

        Notification::assertSentTo($opponent, ChallengeCompletedNotification::class);
    }

    public function test_challenge_winner_takes_twice_stake_amount()
    {
        FeatureFlag::enable(FeatureFlags::CHALLENGE_GAME_STAKING);
        $creator = $this->user;
        $opponent = User::where('id', '<>', $this->user->id)->first();
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

        $creator->wallet()->update([
            'non_withdrawable' => 2500,
        ]);
        $opponent->wallet()->update([
            'non_withdrawable' => 2500,
        ]);


        $amountToStake = 2000;

        $challengeInvitationResponse = $this->postJson(self::SEND_CHALLENGE_INVITE_URL, [
            'opponentId' => $opponent->id,
            'categoryId' => $category->id,
            'staking_amount' => $amountToStake
        ]);

        $challenge = Challenge::where('user_id', $creator->id)->where('opponent_id', $opponent->id)->first();


        $acceptanceResponse = $this->actingAs($opponent)->postJson(self::CHALLENGE_RESPONSE_URL, [
            'status' => true,
            'challenge_id' => $challenge->id
        ]);


        $playerOneGameSession = ChallengeGameSession::create([
            'challenge_id' => $challenge->id,
            'user_id' => $this->user->id,
            'game_type_id' => 2,
            'category_id' => $category->id,
            'wrong_count' => 3,
            'correct_count' => 1,
            'state' => 'COMPLETED',
            'session_token' => Str::random()
        ]);

        $playerTwoGameSession = ChallengeGameSession::create([
            'challenge_id' => $challenge->id,
            'user_id' => $opponent->id,
            'game_type_id' => 2,
            'category_id' => $category->id,
            'wrong_count' => 3,
            'correct_count' => 7,
            'state' => 'COMPLETED',
            'session_token' => Str::random()
        ]);

        $challengeGameService = new ChallengeGameService();
        $challengeGameService->creditStakeWinner($challenge);

        $this->assertDatabaseHas('stakings', [
            'user_id' => $opponent->id,
            'amount_won' => $amountToStake * 2
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $opponent->wallet->id,
            'transaction_type' => 'CREDIT'
        ]);
    }

    public function test_stake_is_shared_when_game_ends_in_draw()
    {
        FeatureFlag::enable(FeatureFlags::CHALLENGE_GAME_STAKING);
        $creator = $this->user;
        $opponent = User::where('id', '<>', $this->user->id)->first();
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

        $creator->wallet()->update([
            'non_withdrawable' => 2500,
        ]);
        $opponent->wallet()->update([
            'non_withdrawable' => 2500,
        ]);


        $amountToStake = 2000;

        $challengeInvitationResponse = $this->postJson(self::SEND_CHALLENGE_INVITE_URL, [
            'opponentId' => $opponent->id,
            'categoryId' => $category->id,
            'staking_amount' => $amountToStake
        ]);

        $challenge = Challenge::where('user_id', $creator->id)->where('opponent_id', $opponent->id)->first();


        $acceptanceResponse = $this->actingAs($opponent)->postJson(self::CHALLENGE_RESPONSE_URL, [
            'status' => true,
            'challenge_id' => $challenge->id
        ]);


        $playerOneGameSession = ChallengeGameSession::create([
            'challenge_id' => $challenge->id,
            'user_id' => $this->user->id,
            'game_type_id' => 2,
            'category_id' => $category->id,
            'wrong_count' => 3,
            'correct_count' => 6,
            'state' => 'COMPLETED',
            'session_token' => Str::random()
        ]);

        $playerTwoGameSession = ChallengeGameSession::create([
            'challenge_id' => $challenge->id,
            'user_id' => $opponent->id,
            'game_type_id' => 2,
            'category_id' => $category->id,
            'wrong_count' => 3,
            'correct_count' => 6,
            'state' => 'COMPLETED',
            'session_token' => Str::random()
        ]);

        $challengeGameService = new ChallengeGameService();
        $challengeGameService->creditStakeWinner($challenge);

        $this->assertDatabaseHas('stakings', [
            'user_id' => $opponent->id,
            'amount_won' => $amountToStake
        ]);

        $this->assertDatabaseHas('stakings', [
            'user_id' => $creator->id,
            'amount_won' => $amountToStake
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $opponent->wallet->id,
            'transaction_type' => 'CREDIT'
        ]);
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $creator->wallet->id,
            'transaction_type' => 'CREDIT'
        ]);
    }

    public function test_challenge_cannot_be_created_when_category_questions_are_not_enough()
    {
        $opponent = User::where('id', '<>', $this->user->id)->first();
        $category = $this->category;

        $response = $this->postJson(self::SEND_CHALLENGE_INVITE_URL, [
            'opponentId' => $opponent->id,
            'categoryId' => $category->id
        ]);

        $response->assertJson([
            'errors' => 'Category is not available',
        ]);
    }

}

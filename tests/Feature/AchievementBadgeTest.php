<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use App\Events\AchievementBadgeEvent;
use App\Listeners\AchievementBadgeEventListener;
use Tests\TestCase;

use PlanSeeder;
use UserSeeder;
use BoostSeeder;
use CategorySeeder;
use GameModeSeeder;
use GameTypeSeeder;
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
use Database\Seeders\AchievementBadgeSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;


class AchievementBadgeTest extends TestCase
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
        $this->seed(AchievementBadgeSeeder::Class);
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

    public function test_is_attached_to_event()
    {
        $has = Event::hasListeners(AchievementBadgeEvent::class);
        $this->assertTrue($has);
    }

    public function test_exhibition_game_can_be_started()
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

    public function test_end_exhibition_game_to_trigger_event()
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

    public function test_achievement_has_been_claimed()
    {
        $achievement = DB::table('user_achievement_badges')
            ->where('user_id', $this->user->id)
            ->where('is_claimed', true)
            ->first();

        $awarded = (is_null($achievement)) ? true : false;

        $this->assertTrue($awarded);
    }

    public function test_achievement_has_been_rewarded()
    {
        $achievement = DB::table('user_achievement_badges')
            ->where('user_id', $this->user->id)
            ->where('is_claimed', true)
            ->where('is_rewarded', true)
            ->first();

        $awarded = (is_null($achievement)) ? true : false;

        $this->assertTrue($awarded);
    }
}

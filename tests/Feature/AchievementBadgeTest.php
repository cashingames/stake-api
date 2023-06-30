<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\Events\AchievementBadgeEvent;
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
use App\Models\Category;

use App\Models\GameSession;
use App\Services\FeatureFlag;
use Database\Seeders\StakingOddSeeder;
use Database\Seeders\StakingOddsRulesSeeder;
use Database\Seeders\AchievementBadgeSeeder;
use Illuminate\Support\Facades\DB;

class AchievementBadgeTest extends TestCase
{

    use RefreshDatabase;
    /**
     * A basic feature test example..
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
        $this->seed(StakingOddsRulesSeeder::class);
        $this->seed(AchievementBadgeSeeder::class);
        GameSession::factory()
            ->count(20)
            ->create();
        $this->user = User::first();
        $this->category = Category::where('category_id', '!=', 0)->inRandomOrder()->first();
        $this->plan = Plan::inRandomOrder()->first();
        $this->actingAs($this->user);
        FeatureFlag::isEnabled(FeatureFlags::ACHIEVEMENT_BADGES);
        config(['odds.maximum_exhibition_staking_amount' => 1000]);
    }

    public function test_is_attached_to_event()
    {
        $has = Event::hasListeners(AchievementBadgeEvent::class);
        $this->assertTrue($has);
    }

    public function test_achievement_has_been_claimed()
    {
        FeatureFlag::enable(FeatureFlags::ACHIEVEMENT_BADGES);
        GameSession::where('user_id', '!=', $this->user->id)->update(['user_id' => $this->user->id]);
        $game = $this->user->gameSessions()->first();
        $game->update(['state' => 'ONGOING']);

        $this->postjson(self::END_EXHIBITION_GAME_URL, [
            "token" => $game->session_token,
            "chosenOptions" => [],
            "consumedBoosts" => []
        ]);

        $achievement = DB::table('user_achievement_badges')
            ->where('user_id', $this->user->id)
            ->where('is_claimed', true)
            ->first();

        $awarded = (!is_null($achievement)) ? true : false;

        $this->assertTrue($awarded);
    }

    public function test_achievement_has_been_rewarded()
    {
        FeatureFlag::enable(FeatureFlags::ACHIEVEMENT_BADGES);
        GameSession::where('user_id', '!=', $this->user->id)->update(['user_id' => $this->user->id]);
        $game = $this->user->gameSessions()->first();
        $game->update(['state' => 'ONGOING']);

        $this->postjson(self::END_EXHIBITION_GAME_URL, [
            "token" => $game->session_token,
            "chosenOptions" => [],
            "consumedBoosts" => []
        ]);

        $achievement = DB::table('user_achievement_badges')
            ->where('user_id', $this->user->id)
            ->where('is_claimed', true)
            ->where('is_rewarded', true)
            ->first();

        $awarded = (!is_null($achievement)) ? true : false;

        $this->assertTrue($awarded);
    }

    public function test_get_achievment_collections()
    {
        $response = $this->get("/api/v3/achievement-badges");

        $response->assertStatus(200);
    }

    public function test_is_achievement_feature_flag_functioning()
    {
        FeatureFlag::disable(FeatureFlags::ACHIEVEMENT_BADGES);
        GameSession::where('user_id', '!=', $this->user->id)->update(['user_id' => $this->user->id]);
        $game = $this->user->gameSessions()->first();
        $game->update(['state' => 'ONGOING']);

        $this->postjson(self::END_EXHIBITION_GAME_URL, [
            "token" => $game->session_token,
            "chosenOptions" => [],
            "consumedBoosts" => []
        ]);

        $achievement = DB::table('user_achievement_badges')
            ->where('user_id', $this->user->id)
            ->where('is_claimed', true)
            ->first();

        $awarded = (is_null($achievement)) ? true : false;

        $this->assertTrue($awarded);
    }
}

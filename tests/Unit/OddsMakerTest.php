<?php

namespace Tests\Unit;

use App\Models\GameSession;
use App\Models\User;
use App\Services\OddsComputer;
use Tests\TestCase;
use Database\Seeders\PlanSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\BoostSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\GameModeSeeder;
use Database\Seeders\GameTypeSeeder;
use Database\Seeders\AchievementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use phpDocumentor\Reflection\Types\Null_;

class OddsMakerTest extends TestCase
{
    use RefreshDatabase;

    public $user, $latestThreeGames;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        $this->seed(GameTypeSeeder::class);
        $this->seed(GameModeSeeder::class);
        $this->seed(PlanSeeder::class);

        $this->user = User::first();

        GameSession::factory()
            ->count(20)
            ->create();
        GameSession::where('user_id', '!=', $this->user->id)->update(['user_id' => $this->user->id]);
        $this->latestThreeGames = $this->user->gameSessions()->latest()->limit(3)->get();
    }

    public function test_odds_for_first_time_player()
    {
        $user = User::factory()->create();
        $oddsComputer = new OddsComputer();
        $oddEffect = $oddsComputer->compute($user, NULL);
        $this->assertEquals(3, $oddEffect['oddsMultiplier']);
    }
    public function test_odds_when_avg_score_less_than_4_in_normal_hours()
    {
        $currentHour = intval(date("H"));

        config(['odds.special_hours' => [
            $currentHour . ":00",
        ]]);


        $this->latestThreeGames->map(function ($game) {
            $game->update(['correct_count' => 2]);
        });

        $avg_score = $this->user->gameSessions()->latest()->limit(3)->get()->avg('correct_count');
        $oddsComputer = new OddsComputer();
        $oddEffect = $oddsComputer->compute($this->user, $avg_score);
        $this->assertEquals(1, $oddEffect['oddsMultiplier']);
    }

    public function test_odds_when_avg_score_between_5_and_7()
    {
        $this->latestThreeGames->map(function ($game) {
            $game->update(['correct_count' => 6.5]);
        });

        $avg_score = $this->user->gameSessions()->latest()->limit(3)->get()->avg('correct_count');
        $oddsComputer = new OddsComputer();
        $oddEffect = $oddsComputer->compute($this->user, $avg_score);
        $this->assertEquals(1.5, $oddEffect['oddsMultiplier']);
    }

    public function test_odds_when_avg_score_greater_than_7()
    {
        $this->latestThreeGames->map(function ($game) {
            $game->update(['correct_count' => 8.2]);
        });

        $avg_score = $this->user->gameSessions()->latest()->limit(3)->get()->avg('correct_count');

        $oddsComputer = new OddsComputer();
        $oddEffect = $oddsComputer->compute($this->user, $avg_score);
        $this->assertEquals(1, $oddEffect['oddsMultiplier']);
    }

    // public function test_odds_when_current_time_is_special(){
    //     $currentHour = date("H");

    //     config(['odds.special_hours' => [
    //         $currentHour . ":00",
    //         (intval($currentHour) < 10 ? "0" : "") . (intval($currentHour) + 1) . ":00"
    //     ]]);

    //     $oddsComputer = new OddsComputer();
    //     $oddEffect = $oddsComputer->compute($this->user, 3);

    //     $this->assertEquals(1.5, $oddEffect['oddsMultiplier']);

    // }
}

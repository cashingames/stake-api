<?php

namespace Tests\Unit;

use App\Models\GameSession;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\StakingOddsComputer;
use Tests\TestCase;
use Database\Seeders\PlanSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\GameModeSeeder;
use Database\Seeders\GameTypeSeeder;
use Database\Seeders\StakingOddsRulesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StakingOddsMakerTest extends TestCase
{
    use RefreshDatabase;

    public $user, $latestThreeGames, $specialHours, $currentHour;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        $this->seed(GameTypeSeeder::class);
        $this->seed(GameModeSeeder::class);
        $this->seed(PlanSeeder::class);
        $this->seed(StakingOddsRulesSeeder::class);

        $this->user = User::first();

        GameSession::factory()
            ->count(20)
            ->create([
                'user_id' => $this->user->id
            ]);
        $this->latestThreeGames = $this->user->gameSessions()->latest()->limit(3)->get();
        $this->specialHours = config('odds.special_hours');
        $this->currentHour = date("H");
    }

    public function test_odds_for_first_time_player()
    {
        $user = User::factory()->create();
        $oddsComputer = new StakingOddsComputer();
        $oddEffect = $oddsComputer->compute($user);
        $expectation = 3;

        $this->assertEquals($expectation, $oddEffect['oddsMultiplier']);
    }
    public function test_odds_when_game_count_less_than_4()
    {
        $this->latestThreeGames->map(function ($game) {
            $game->update(['correct_count' => 3]);
        });

        $oddsComputer = new StakingOddsComputer();
        $oddEffect = $oddsComputer->compute($this->user);
        $expectation = 3;
        $this->assertEquals($expectation, $oddEffect['oddsMultiplier']);
    }

}

<?php

namespace Tests\Unit;

use App\Models\GameSession;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\OddsComputer;
use Tests\TestCase;
use Database\Seeders\UserSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\GameModeSeeder;
use Database\Seeders\GameTypeSeeder;
use Database\Seeders\OddsConditionsAndRulesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OddsMakerTest extends TestCase
{
    use RefreshDatabase;

    public $user;
    public $latestThreeGames;
    public $specialHours;
    public $currentHour;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(CategorySeeder::class);
        $this->seed(GameTypeSeeder::class);
        $this->seed(GameModeSeeder::class);
        $this->seed(OddsConditionsAndRulesSeeder::class);

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

    public function test_odds_for_exhibition_first_time_player_()
    {
        $user = User::factory()->create();
        $oddsComputer = new OddsComputer();
        $oddEffect = $oddsComputer->compute($user, NULL, false);
        $expectation = 3;
        
        $currentHour = (intval($this->currentHour) < 9 ? "0" : "") . (intval($this->currentHour) + 1) . ":00";
        if (in_array($currentHour, $this->specialHours)){
            $expectation += 1.5;
        }
        $this->assertEquals($expectation, $oddEffect['oddsMultiplier']);
    }

    public function test_odds_for_live_trivia_first_time_player_()
    {
        $user = User::factory()->create();
        $oddsComputer = new OddsComputer();
        $oddEffect = $oddsComputer->compute($user, NULL, true);
        $expectation = 3;
        
        $this->assertEquals($expectation, $oddEffect['oddsMultiplier']);
    }

    public function test_odds_when_avg_score_less_than_5_in_normal_hours()
    {
        $currentHour = intval(date("H"));

        config(['odds.special_hours' => [
            intval($currentHour) . ":00",
        ]]);


        $this->latestThreeGames->map(function ($game) {
            $game->update(['correct_count' => 3]);
        });

        $avg_score = $this->user->gameSessions()->latest()->limit(3)->get()->avg('correct_count');
        $oddsComputer = new OddsComputer();
        $oddEffect = $oddsComputer->compute($this->user, $avg_score,false);
        $expectation = 2.5;
        if (in_array($this->currentHour, $this->specialHours)) {
            $expectation += 1.5;
        }
        $this->assertEquals($expectation, $oddEffect['oddsMultiplier']);
    }

    public function test_odds_when_avg_score_between_5_and_7()
    {
        $this->latestThreeGames->map(function ($game) {
            $game->update(['correct_count' => 6.5]);
        });

        $avg_score = $this->user->gameSessions()->latest()->limit(3)->get()->avg('correct_count');
        $oddsComputer = new OddsComputer();
        $oddEffect = $oddsComputer->compute($this->user, $avg_score,false);
        $expectation = 1;
        if (in_array($this->currentHour, $this->specialHours)) {
            $expectation += 1.5;
        }
        $this->assertEquals($expectation, $oddEffect['oddsMultiplier']);
    }

    public function test_odds_when_avg_score_greater_than_7()
    {
        $this->latestThreeGames->map(function ($game) {
            $game->update(['correct_count' => 8.2]);
        });

        $avg_score = $this->user->gameSessions()->latest()->limit(3)->get()->avg('correct_count');

        $oddsComputer = new OddsComputer();
        $oddEffect = $oddsComputer->compute($this->user, $avg_score, false);
        $expectation = 1;
        if (in_array($this->currentHour, $this->specialHours)) {
            $expectation += 1.5;
        }
        $this->assertEquals($expectation, $oddEffect['oddsMultiplier']);
    }

    public function test_odds_after_immediate_wallet_funding(){
        $this->latestThreeGames->map(function ($game) {
            $game->update(['correct_count' => 6.5]);
        });

        WalletTransaction::factory()->create([
            'wallet_id' => $this->user->wallet->id,
            'transaction_type' => 'CREDIT',
            'description' => 'fund wallet',
            'created_at' => now()->addHours(1),
            'updated_at' => now()->addHours(1)
        ]);
        $avg_score = $this->user->gameSessions()->latest()->limit(3)->get()->avg('correct_count');
        $oddsComputer = new OddsComputer();
        $oddEffect = $oddsComputer->compute($this->user, $avg_score, false);
        $expectation = 1.5;
        if (in_array($this->currentHour, $this->specialHours)) {
            $expectation += 0.5;
        }
        $this->assertEquals($expectation, $oddEffect['oddsMultiplier']);

    }

    public function test_odds_when_current_time_is_special(){
        $currentHour = date("H");
        config(['odds.special_hours' => [
            $currentHour . ":00",
            (intval($currentHour) < 9 ? "0" : "") . (intval($currentHour) + 1) . ":00"
        ]]);
        
        
        $oddsComputer = new OddsComputer();
        $oddEffect = $oddsComputer->compute($this->user, 3, false);

        $expectation = 4;
        
        $this->assertEquals($expectation, $oddEffect['oddsMultiplier']);

    }

    public function test_live_trivia_odds_does_not_include_special_hour(){
        $currentHour = date("H");
        config(['odds.special_hours' => [
            $currentHour . ":00",
            (intval($currentHour) < 9 ? "0" : "") . (intval($currentHour) + 1) . ":00"
        ]]);
        
        
        $oddsComputer = new OddsComputer();
        $oddEffect = $oddsComputer->compute($this->user, 3, true);
            
        $this->assertNotEquals('special_hour', $oddEffect['oddsMultiplier']);

    }
}

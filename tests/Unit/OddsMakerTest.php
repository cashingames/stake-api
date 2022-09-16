<?php

namespace Tests\Unit;

use App\Models\GameSession;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\OddsComputer;
use Tests\TestCase;
use Database\Seeders\PlanSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\GameModeSeeder;
use Database\Seeders\GameTypeSeeder;
use Database\Seeders\OddsConditionsAndRulesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OddsMakerTest extends TestCase
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
        $this->seed(OddsConditionsAndRulesSeeder::class);

        $this->user = User::first();

        GameSession::factory()
            ->count(20)
            ->create([
                'user_id' => $this->user->id
            ]);
        // GameSession::where('user_id', '!=', $this->user->id)->update(['user_id' => $this->user->id]);
        $this->latestThreeGames = $this->user->gameSessions()->latest()->limit(3)->get();
        $this->specialHours = config('odds.special_hours');
        $this->currentHour = date("H");
    }

    public function test_odds_for_first_time_player()
    {
        $user = User::factory()->create();
        $oddsComputer = new OddsComputer();
        $oddEffect = $oddsComputer->compute($user, NULL);
        $expectation = 3;
        echo "Current hour: {$this->currentHour}\n";
        $currentHour = (intval($this->currentHour) < 9 ? "0" : "") . (intval($this->currentHour) + 1) . ":00";
        if (in_array($currentHour, $this->specialHours)){
            $expectation += 1.5;
        }
        // dd($expectation, $oddEffect);
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
        $oddEffect = $oddsComputer->compute($this->user, $avg_score);
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
        $oddEffect = $oddsComputer->compute($this->user, $avg_score);
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
        $oddEffect = $oddsComputer->compute($this->user, $avg_score);
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
        $oddEffect = $oddsComputer->compute($this->user, $avg_score);
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
        $oddEffect = $oddsComputer->compute($this->user, 3);

        $expectation = 4;
        
        $this->assertEquals($expectation, $oddEffect['oddsMultiplier']);

    }
}

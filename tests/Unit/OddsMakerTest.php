<?php

namespace Tests\Unit;

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
use Illuminate\Support\Facades\Artisan;
use phpDocumentor\Reflection\Types\Null_;

class OddsMakerTest extends TestCase
{
    public $user;
    
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        
        $this->user = User::first();
    }

    public function test_odds_for_first_time_player(){
        $user = User::factory()->create();
        $oddsComputer = new OddsComputer();
        $oddEffect = $oddsComputer->compute($user, NULL);
        $this->assertEquals(10, $oddEffect['oddsMultiplier']);
    }
    public function test_odds_when_avg_score_less_than_4_in_normal_hours(){
        $currentHour = intval(date("H"));

        config(['odds.special_hours' => [
            $currentHour . ":00",
        ]]);
        $avg_score = 3;
        $oddsComputer = new OddsComputer();
        $oddEffect = $oddsComputer->compute($this->user, $avg_score);
        $this->assertEquals(1, $oddEffect['oddsMultiplier']);
    }

    public function test_odds_when_avg_score_between_5_and_7(){
        $avg_score = 6.5;
        $oddsComputer = new OddsComputer();
        $oddEffect = $oddsComputer->compute($this->user, $avg_score);
        $this->assertEquals(1.5, $oddEffect['oddsMultiplier']);
    }

    public function test_odds_when_avg_score_greater_than_7()
    {
        $avg_score = 8.2;
        $oddsComputer = new OddsComputer();
        $oddEffect = $oddsComputer->compute($this->user, $avg_score);
        $this->assertEquals(1, $oddEffect['oddsMultiplier']);
    }

    public function test_odds_when_current_time_is_special(){
        $currentHour = date("H");
        
        config(['odds.special_hours' => [
            $currentHour . ":00",
            "0" . (intval($currentHour) + 1) . ":00"
        ]]);
        
        $oddsComputer = new OddsComputer();
        $oddEffect = $oddsComputer->compute($this->user, 3);
        // dd($oddEffect, config('odds.special_hours'), $currentHour);
        $this->assertEquals(1.5, $oddEffect['oddsMultiplier']);
        
    }
}

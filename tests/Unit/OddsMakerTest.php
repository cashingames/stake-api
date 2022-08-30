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

class OddsMakerTest extends TestCase
{
    public $user;
    
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        // Artisan::call("db:seed --class=UserSeeder")
        
        $this->user = User::first();
    }

    public function test_odds_when_avg_score_less_than_4(){
        $avg_score = 3;
        $oddsComputer = new OddsComputer();
        $oddMultiplier = $oddsComputer->compute($this->user, $avg_score);
        $this->assertTrue($oddMultiplier === 1);
    }

    public function test_odds_when_avg_score_between_5_and_7(){
        $avg_score = 6.5;
        $oddsComputer = new OddsComputer();
        $oddMultiplier = $oddsComputer->compute($this->user, $avg_score);
        $this->assertTrue($oddMultiplier === 1.5);
    }

    public function test_odds_when_avg_score_greater_than_7()
    {
        $avg_score = 8.2;
        $oddsComputer = new OddsComputer();
        $oddMultiplier = $oddsComputer->compute($this->user, $avg_score);
        $this->assertTrue($oddMultiplier === 1);
    }
}

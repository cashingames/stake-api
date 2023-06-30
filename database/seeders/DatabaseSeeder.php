<?php

namespace Database\Seeders;

use App\Models\ContestPrizePool;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $this->call([
            CategorySeeder::class,
            GameTypeSeeder::class,
            GameModeSeeder::class,
            AchievementBadgeSeeder::class,
            BoostSeeder::class,
            UserSeeder::class,
            PlanSeeder::class,
            OddsConditionsAndRulesSeeder::class,
            // WalletSeeder::class,
            // ProfileSeeder::class,
            QuestionSeeder::class,
            RewardSeeder::class,
            RewardBenefitSeeder::class,
            GameSeeder::class
        ]);
    }
}

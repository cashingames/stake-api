<?php

namespace Database\Seeders;

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
            AchievementSeeder::class,
            BoostSeeder::class,
            UserSeeder::class,
            PlanSeeder::class,
            OddsConditionsAndRulesSeeder::class,
            StakingOddsRulesSeeder::class,
            ChallengeSeeder::class,
            TriviaSeeder::class,
            NotificationSeeder::class,
            WalletTransactionSeeder::class,
            WalletSeeder::class,
            ProfileSeeder::class,
            // QuestionSeeder::class,
            StakingSeeder::class,
            StakingOddSeeder::class,
        ]);
    }
}

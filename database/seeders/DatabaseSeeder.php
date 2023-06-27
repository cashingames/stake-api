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
            AchievementSeeder::class,
            AchievementBadgeSeeder::class,
            BoostSeeder::class,
            UserSeeder::class,
            PlanSeeder::class,
            OddsConditionsAndRulesSeeder::class,
            StakingOddsRulesSeeder::class,
            ChallengeSeeder::class,
            ContestSeeder::class,
            //ContestPrizePoolSeeder::class,
            TriviaSeeder::class,
            // WalletTransactionSeeder::class,
            // WalletSeeder::class,
            // ProfileSeeder::class,
            QuestionSeeder::class,
            StakingSeeder::class,
            StakingOddSeeder::class,
            ChallengeRequestSeeder::class,
            RewardSeeder::class,
            RewardBenefitSeeder::class,
            GameSeeder::class
        ]);
    }
}

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
            BoostSeeder::class,
            UserSeeder::class,
            PlanSeeder::class,
            OddsConditionsAndRulesSeeder::class,
            StakingOddsRulesSeeder::class,
            ContestSeeder::class,
            //ContestPrizePoolSeeder::class,
            // WalletTransactionSeeder::class,
            // WalletSeeder::class,
            // ProfileSeeder::class,
            QuestionSeeder::class,
            StakingSeeder::class,
            StakingOddSeeder::class,
            ChallengeRequestSeeder::class,
            BonusSeeder::class,
            PromotionSeeder::class
        ]);
    }
}

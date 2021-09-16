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
            BoostSeeder::class,
            GameTypeSeeder::class,
            ModeSeeder::class,
            UserSeeder::class,
            ChallengeSeeder::class,
            NotificationSeeder::class,
            // WalletTransactionSeeder::class,
            // WalletSeeder::class,
            // ProfileSeeder::class,
            AchievementSeeder::class,
           // QuestionSeeder::class

            
        ]);

        //\App\Models\Question::factory(150)->create();
    }
}

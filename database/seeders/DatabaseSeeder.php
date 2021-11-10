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
            // NotificationSeeder::class,
            // WalletTransactionSeeder::class,
            // WalletSeeder::class,
            // ProfileSeeder::class,
            // QuestionSeeder::class


        ]);
    }
}

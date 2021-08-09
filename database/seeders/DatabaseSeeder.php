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
        //\App\Models\User::factory(10)->create();

        $this->call([
            CategorySeeder::class,
            BoostSeeder::class,
            UserSeeder::class,
            // WalletTransactionSeeder::class,
            // WalletSeeder::class,
            // ProfileSeeder::class,
            ModeSeeder::class,
            GameTypeSeeder::class,
            AchievementSeeder::class

            
        ]);

        \App\Models\Question::factory(150)->create();
    }
}

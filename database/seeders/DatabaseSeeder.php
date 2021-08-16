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
            UserSeeder::class,
            // WalletTransactionSeeder::class,
            // WalletSeeder::class,
            // ProfileSeeder::class,
            ModeSeeder::class,
            GameTypeSeeder::class,
            AchievementSeeder::class,
            QuestionSeeder::class

            
        ]);

        //\App\Models\Question::factory(150)->create();
    }
}

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

        // $this->call(PlanSeeder::class);
        // $this->call(CategorySeeder::class);
        $this->call(QuestionSeeder::class);
        // $this->call(VoucherSeeder::class);
        // $this->call(UserSeeder::class);
    }
}

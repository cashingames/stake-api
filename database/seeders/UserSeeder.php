<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        User::factory()
        ->count(5)
        ->hasProfile(1)
        ->hasWallet(1)
        ->hasTransactions(5)
        // ->hasUserPlan(1)
        ->create();
    }
}

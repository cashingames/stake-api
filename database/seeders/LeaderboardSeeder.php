<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaderboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()
        ->count(5)
        ->hasProfile(1)
        // ->hasWallet(1)
        // ->hasTransactions(5)
        // ->hasUserPlan(1)
        ->hasGameSessions(10)
        ->create();
    }
}

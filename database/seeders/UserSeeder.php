<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Carbon\Carbon;
use App\Models\GameSession;
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
        ->hasUserPlan(1)
        ->has(GameSession::factory()
            ->count(10)->sequence(fn ($sequence) => [
                'created_at' => Carbon::now()->subMinutes(5),
                'created_at' => Carbon::now(),
                ])
        )
        // , hasGameSessions(10)->s
        ->create();
    }
}

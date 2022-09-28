<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StakingOddSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $odds = config('odds.standard');
        foreach ($odds as $key => $value) {
            DB::table('staking_odds')->insert([
                'score' => $key,
                'odd' => $value
            ]);
        }
    }
}

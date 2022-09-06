<?php

namespace Database\Seeders;

use App\Models\Staking;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StakingSeeder extends Seeder
{   
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        Staking::factory()
        ->count(5)
        ->create();
    }
}

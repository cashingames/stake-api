<?php

namespace Database\Seeders;

use App\Models\ContestPrizePool;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContestPrizePoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ContestPrizePool::factory()
        ->count(5)
        ->create();
    }
}

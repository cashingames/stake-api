<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\StakingOdd;

class StakingOddSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        StakingOdd::factory()->count(11)
                ->sequence(fn ($sequence) => [
                    'score' => $sequence->index,
                    'odd' => $sequence->index*2,
                ])->create();
    }
}

<?php

namespace Database\Seeders;

use App\Models\Contest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Contest::factory()
        ->count(5)
        ->create();
    }
}

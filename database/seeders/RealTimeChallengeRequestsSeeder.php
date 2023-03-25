<?php

namespace Database\Seeders;

use App\Models\RealtimeChallengeRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RealTimeChallengeRequestsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RealtimeChallengeRequest::factory()
            ->count(5)
            ->create();
    }
}

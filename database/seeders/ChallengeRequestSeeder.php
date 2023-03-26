<?php

namespace Database\Seeders;

use App\Models\ChallengeRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChallengeRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ChallengeRequest::factory()
        ->count(5)
        ->create();
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LiveTrivia;

class LiveTriviaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        LiveTrivia::factory()
        ->count(5)
        // ->hasTriviaQuestions(10)
        // ->hasGameSessions(10)
        ->create();

    }
}

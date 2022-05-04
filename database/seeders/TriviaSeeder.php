<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Trivia;

class TriviaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Trivia::factory()
        ->count(5)
        // ->hasTriviaQuestions(10)
        // ->hasGameSessions(10)
        ->create();

    }
}

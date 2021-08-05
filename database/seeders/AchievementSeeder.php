<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('achievements')->insert(
            [
                'title' => "Sage",
                'point_milestone' => 2000,
                'medal'=>'sage_medal.png',
            ],
        
        );
        DB::table('achievements')->insert(
            [
                'title' => "Regal",
                'point_milestone' => 5000,
                'medal'=> 'regal_medal.png',
            ]
        );

        DB::table('achievements')->insert(
            [
                'title' => "Knight",
                'point_milestone' => 10000,
                'medal'=> 'knight_medal.png',
            ]
        );

        DB::table('achievements')->insert(
            [
                'title' => "Conqueror",
                'point_milestone' => 50000,
                'medal'=> 'conqueror_medal.png',
            ]
        );
    }
}

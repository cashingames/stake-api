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
                'medal'=>'medals/sage_medal.png',
            ],
        
        );
        DB::table('achievements')->insert(
            [
                'title' => "Regal",
                'point_milestone' => 5000,
                'medal'=> 'medals/regal_medal.png',
            ]
        );

        DB::table('achievements')->insert(
            [
                'title' => "Knight",
                'point_milestone' => 10000,
                'medal'=> 'medals/knight_medal.png',
            ]
        );

        DB::table('achievements')->insert(
            [
                'title' => "Conqueror",
                'point_milestone' => 100000,
                'medal'=> 'medals/conqueror_medal.png',
            ]
        );
    }
}

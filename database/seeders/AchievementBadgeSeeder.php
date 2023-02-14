<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AchievementBadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('achievement_badges')->insert(
            [
                'title' => "Sage",
                'milestone' => 100,
                'milestone_type' => "POINTS",
                'reward' => 100,
                'reward_type' => "POINTS",
                'description' => "Score above 7 in 100 games in general knowledge",
                'medal'=>'medals/sage_medal.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ],

        );
        DB::table('achievement_badges')->insert(
            [
                'title' => "Regal",
                'milestone' => 500,
                'milestone_type' => "POINTS",
                'reward' => 500,
                'reward_type' => "POINTS",
                'description' => "Score above 7 in 100 games in general knowledge",
                'medal'=> 'medals/regal_medal.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "Knight",
                'milestone' => 1000,
                'milestone_type' => "POINTS",
                'reward' => 1000,
                'reward_type' => "POINTS",
                'description' => "Score above 7 in 100 games in general knowledge",
                'medal'=> 'medals/knight_medal.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "Conqueror",
                'milestone' => 100000,
                'milestone_type' => "POINTS",
                'reward' => 100000,
                'reward_type' => "POINTS",
                'description' => "Score above 7 in 100 games in general knowledge",
                'medal'=> 'medals/conqueror_medal.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );
    }
}

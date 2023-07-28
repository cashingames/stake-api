<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GameModeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //According to earlier discussion when this was implemented, Tournament was not a mode this was why it was not included

        DB::table('game_modes')->insert(
            [
                'name' => "EXHIBITION",
                'game_id' => 1,
                'display_name' => "Exhibition",
                'description' => "Play Single",
            ]
        );

        DB::table('game_modes')->insert(
            [
                'name' => "CHALLENGE",
                'game_id' => 1,
                'display_name' => "Challenge",
                'description' => "Challenge a friend to a duel",
            ]
        );

        DB::table('game_modes')->insert(
            [
                'name' => "NORMAL_MODE",
                'game_id' => 2,
                'display_name' => "Normal Mode",
                'description' => "Play different levels",
            ]
        );

        DB::table('game_modes')->insert(
            [
                'name' => "PRACTICE",
                'game_id' => 2,
                'display_name' => "Practice Mode",
                'description' => "Practice your shooting skills",
            ]
        );
    }
}
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
                'display_name' => "Exhibition",
                'description' => "Play Single",
            ]
        );

        DB::table('game_modes')->insert(
            [
                'name' => "CHALLENGE",
                'display_name' => "Challenge",
                'description' => "Challenge a friend to a duel",
            ]
        );
    }
}

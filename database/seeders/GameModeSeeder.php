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
                'icon' => 'icons/exhibition_icon.png',
                'background_color' => '#E2F5EA'
            ]
        );

        DB::table('game_modes')->insert(
            [
                'name' => "CHALLENGE",
                'display_name' => "Challenge",
                'description' => "Challenge a friend to a duel",
                'icon' => 'icons/challenge_icon.png',
                'background_color' => '#FAEEFF'
            ]
        );

        DB::table('game_modes')->insert(
            [
                'name' => "TOURNAMENT",
                'display_name' => "Tournament",
                'description' => "Participate in a tournament",
                'icon' => 'icons/tournament_icon.png',
                'background_color' => '#FCF4DB'
            ]
        );
    }
}

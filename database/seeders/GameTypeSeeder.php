<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GameTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //This will also be reviewed if need be

        DB::table('game_types')->insert(
            [
                'name' => "True or False",
                'description' => "Select from two options whether true or false",
            ]
        );

        DB::table('game_types')->insert(
            [
                'name' => "Multi Choice",
                'description' => "Select one correct answer from other options",
            ]
        );

    }
}

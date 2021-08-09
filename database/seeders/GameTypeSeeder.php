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
        //

        DB::table('game_types')->insert(
            [
                'name' => "True or False",
                'description' => "Select from two options whether true or false",
            ]
        );

        DB::table('game_types')->insert(
            [
                'name' => "Select One Option",
                'description' => "Select one correct answer from other options",
            ]
        );

    }
}

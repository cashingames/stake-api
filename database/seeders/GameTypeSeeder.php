<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

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
                'icon'=> 'icons/true_or_false_icon.png',
                'primary_color_1'=>'#A35EBB',
                'primary_color_2'=>'#C282D5',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('game_types')->insert(
            [
                'name' => "Multi Choice",
                'description' => "Select one correct answer from other options",
                'icon'=> 'icons/multi_choice_icon.png',
                'primary_color_1'=>'#F8A700',
                'primary_color_2'=>'#FBC602',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

    }
}

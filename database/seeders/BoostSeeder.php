<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BoostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* This was initially implemented to split the 
            the boost unit to the least possible value ie 1 like so:

             DB::table('boosts')->insert(
            [
                'name' => "Time Freeze",
                'description' => "Freezes game for 3 seconds",
                'point_value' => 100,
                'currency_value'=> 49.3,
            ]
        );

        DB::table('boosts')->insert(
            [
                'name' => "Bombs",
                'description' => "Removes one wrong and one right answer",
                'point_value' => 100,
                'currency_value'=> 115.0
            ]
        );

        DB::table('boosts')->insert(
            [
                'name' => "Skip",
                'description' => "Skips a question",
                'point_value' => 83.3333333,
                'currency_value'=> 65.7333333
            ]
        );
            ** This was reversed based on the challenge that:
            at the point of buying boosts, there was no way to ascertain the number of boosts
            a user is buying, therefore
            It made more sense going with the product design of buying boosts in packs
        */
        
        DB::table('boosts')->insert(
            [
                'name' => "Time Freeze",
                'description' => "Freezes game for 15 seconds",
                'point_value' => 500,
                'currency_value'=> 246.50,
                'pack_count' => 5
            ]
        );

        DB::table('boosts')->insert(
            [
                'name' => "Bombs",
                'description' => "Removes one wrong and one right answer",
                'point_value' => 300,
                'currency_value'=> 345.10,
                'pack_count' => 3
            ]
        );

        DB::table('boosts')->insert(
            [
                'name' => "Skip",
                'description' => "Skips a question",
                'point_value' => 250,
                'currency_value'=> 197.20,
                'pack_count' => 3
            ]
        );

    }
}

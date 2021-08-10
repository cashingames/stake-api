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
        //
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

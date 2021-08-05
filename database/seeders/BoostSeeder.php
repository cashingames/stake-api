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

    }
}

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

        DB::table('boosts')->insert(
            [
                'name' => "Time Freeze",
                'description' => "Freezes game time For 15 Seconds",
                'point_value' => 100,
                'currency_value' => 60.0,
                'pack_count' => 5,
                'icon' => 'icons/time_freeze_icon.png'
            ]
        );

        DB::table('boosts')->insert(
            [
                'name' => "Bomb",
                'description' => "Takes out two options",
                'point_value' => 100,
                'currency_value' => 50.0000000,
                'pack_count' => 3,
                'icon' => 'icons/bomb_icon.png'
            ]
        );

        DB::table('boosts')->insert(
            [
                'name' => "Skip",
                'description' => "Skips a question",
                'point_value' => 83.3333333,
                'currency_value' => 50.0000000,
                'pack_count' => 3,
                'icon' => 'icons/skip_icon.png'
            ]
        );
    }
}

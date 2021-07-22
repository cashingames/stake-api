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
                'name' => "",
                'point_value' => 0,
                'currency_value'=> 0

            ]
        );

        DB::table('boosts')->insert(
            [
                'name' => "",
                'point_value' => 0,
                'currency_value'=> 0

            ]
        );
        DB::table('boosts')->insert(
            [
                'name' => "",
                'point_value' => 0,
                'currency_value'=> 0

            ]
        );
        DB::table('boosts')->insert(
            [
                'name' => "",
                'point_value' => 0,
                'currency_value'=> 0

            ]
        );
        DB::table('boosts')->insert(
            [
                'name' => "",
                'point_value' => 0,
                'currency_value'=> 0

            ]
        );
        DB::table('boosts')->insert(
            [
                'name' => "",
                'point_value' => 0,
                'currency_value'=> 0

            ]
        );
        DB::table('boosts')->insert(
            [
                'name' => "",
                'point_value' => 0,
                'currency_value'=> 0

            ]
        );
        DB::table('boosts')->insert(
            [
                'name' => "",
                'point_value' => 0,
                'currency_value'=> 0

            ]
        );


    }
}

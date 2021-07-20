<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('points')->insert(
            [
                'description' => "5 value points",
                'value' => 5,

            ]
        );

        DB::table('points')->insert(
            [
                'description' => "50 value points",
                'value' => 50,

            ]
        );
        DB::table('points')->insert(
            [
                'description' => "100 value points",
                'value' => 100,

            ]
        );
        DB::table('points')->insert(
            [
                'description' => "150 value points",
                'value' => 150,

            ]
        );
        DB::table('points')->insert(
            [
                'description' => "200 value points",
                'value' => 200,

            ]
        );
        DB::table('points')->insert(
            [
                'description' => "250 value points",
                'value' => 250,
            ]
        );
        DB::table('points')->insert(
            [
                'description' => "300 value points",
                'value' => 300,
            ]
        );
        DB::table('points')->insert(
            [
                'description' => "350 value points",
                'value' => 350,
            ]
        );
        DB::table('points')->insert(
            [
                'description' => "400 value points",
                'value' => 400,
            ]
        );
        DB::table('points')->insert(
            [
                'description' => "500 value points",
                'value' => 500,
            ]
        );


    }
}

<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PlansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('plans')->insert(
            [
                'name' => 'Silver',
                'description' => 'Silver band',
                'price' => 150.00,
                'games_count' => 4,
                'point_per_question' => 1,
                'minimum_win_points' => 8,
                'price_per_point' => 1.00,
            ]
        );

        DB::table('plans')->insert(
            [
                'name' => 'Bronze',
                'description' => 'Bronze band',
                'price' => 250.00,
                'games_count' => 6,
                'point_per_question' => 2,
                'minimum_win_points' => 16,
                'price_per_point' => 1.00,
            ]
        );

        DB::table('plans')->insert(
            [
                'name' => 'Gold',
                'description' => 'Gold band',
                'price' => 500.00,
                'games_count' => 8,
                'point_per_question' => 3,
                'minimum_win_points' => 24,
                'price_per_point' => 1.00,
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        DB::table('plans')->insert(
            [
                'name' => 'Free',
                'description' => 'Play a game',
                'price' => 0.00,
                'game_count' => 1,
                'background_color'=> '#FAC502',
                'is_free'=>true
            ]
        );

        DB::table('plans')->insert(
            [
                'name' => 'Least Plan',
                'description' => 'Play 2 times',
                'price' => 100.00,
                'game_count' => 2,
                'background_color'=>'#EF2F55',
                'is_free'=>false
            ]
        );

        DB::table('plans')->insert(
            [
                'name' => 'Mini Plan',
                'description' => 'Play 5 times',
                'price' => 200.00,
                'game_count' => 5,
                'background_color'=>'#2D9CDB',
                'is_free'=>false
            ]
        );

        DB::table('plans')->insert(
            [
                'name' => 'Double O',
                'description' => 'Play 12 times',
                'price' => 500.00,
                'game_count' => 12,
                'background_color'=>'#A35EBB',
                'is_free'=>false
            ]
        );

        DB::table('plans')->insert(
            [
                'name' => 'Dicey Multiples',
                'description' => 'Play 18 times',
                'price' => 800.00,
                'game_count' => 18,
                'background_color'=> '#2D9CDB',
                'is_free'=>false
            ]
        );

        DB::table('plans')->insert(
            [
                'name' => 'The Ultimate',
                'description' => 'Play 25 times',
                'price' => 1000.00,
                'game_count' => 25,
                'background_color'=>'#EF2F55',
                'is_free'=>false
            ]
        );

    }
}

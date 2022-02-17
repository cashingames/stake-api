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
                'description' => 'Play 10 times',
                'price' => 0.00,
                'game_count' => 1,
                'background_color'=> '#FAC502',
                'is_free'=>true
            ]
        );

        DB::table('plans')->insert(
            [
                'name' => 'Double O',
                'description' => 'Play 15 times',
                'price' => 500.00,
                'game_count' => 15,
                'background_color'=>'#A35EBB',
                'is_free'=>false
            ]
        );

        DB::table('plans')->insert(
            [
                'name' => 'Dicey Multiples',
                'description' => 'Play 20 times',
                'price' => 800.00,
                'game_count' => 20,
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

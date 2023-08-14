<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CashDropSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('cashdrops')->insert(
            [
                'name' => "Gold",
                'lower_pool_limit' => 4900,
                'upper_pool_limit' => 6890,
                'percentage_stake' => 0.05,
                'created_at' => now(),
                'updated_at' => now(),
                'icon' => 'icons/gold_cashdrop_icon.png'
            ]
        );
        DB::table('cashdrops')->insert(
            [
                'name' => "Silver",
                'lower_pool_limit' => 1900,
                'upper_pool_limit' => 4890,
                'percentage_stake' => 0.03,
                'created_at' => now(),
                'updated_at' => now(),
                'icon' => 'icons/silver_cashdrop_icon.png'
            ]
        );
        DB::table('cashdrops')->insert(
            [
                'name' => "Bronze",
                'lower_pool_limit' => 500,
                'upper_pool_limit' => 1890,
                'percentage_stake' => 0.02,
                'created_at' => now(),
                'updated_at' => now(),
                'icon' => 'icons/bronze_cashdrop_icon.png'
            ]
        );

    }
}

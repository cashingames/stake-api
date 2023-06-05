<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RewardBenefitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('reward_benefits')->insert(
            [
                'id' => 1,
                'reward_id' => 1,
                'reward_benefit_id' => 1,
                'total_hours' =>  24,
                'reward_type' => 'boost',
                'reward_name' => 'Skip',
                'reward_count' => 1
            ]
        );

        DB::table('reward_benefits')->insert(
            [
                'id' => 2,
                'reward_id' => 1,
                'reward_benefit_id' => 2,
                'total_hours' =>  48,
                'reward_type' => 'boost',
                'reward_name' => 'Bomb',
                'reward_count' => 1
            ]
        );

        DB::table('reward_benefits')->insert(
            [
                'id' => 3,
                'reward_id' => 1,
                'reward_benefit_id' => 3,
                'total_hours' =>  72,
                'reward_type' => 'boost',
                'reward_name' => 'Time Freeze',
                'reward_count' => 2
            ]
        );

        DB::table('reward_benefits')->insert(
            [
                'id' => 4,
                'reward_id' => 1,
                'reward_benefit_id' => 4,
                'total_hours' =>  96,
                'reward_type' => 'coins',
                'reward_count' => 20
            ]
        );

        DB::table('reward_benefits')->insert(
            [
                'id' => 5,
                'reward_id' => 1,
                'reward_benefit_id' => 5,
                'total_hours' =>  120,
                'reward_type' => 'coins',
                'reward_count' => 30
            ]
        );

        DB::table('reward_benefits')->insert(
            [
                'id' => 6,
                'reward_id' => 1,
                'reward_benefit_id' => 5,
                'total_hours' =>  120,
                'reward_type' => 'boost',
                'reward_name' => 'Bomb',
                'reward_count' => 3
            ]
        );

        DB::table('reward_benefits')->insert(
            [
                'id' => 7,
                'reward_id' => 1,
                'reward_benefit_id' => 6,
                'total_hours' =>  144,
                'reward_type' => 'coins',
                'reward_count' => 60
            ]
        );

        DB::table('reward_benefits')->insert(
            [
                'id' => 8,
                'reward_id' => 1,
                'reward_benefit_id' => 6,
                'total_hours' =>  144,
                'reward_type' => 'boost',
                'reward_name' => 'Skip',
                'reward_count' => 3
            ]
        );

        DB::table('reward_benefits')->insert(
            [
                'id' => 9,
                'reward_id' => 1,
                'reward_benefit_id' => 7,
                'total_hours' =>  168,
                'reward_type' => 'coins',
                'reward_count' => 80
            ]
        );

        DB::table('reward_benefits')->insert(
            [
                'id' => 10,
                'reward_id' => 1,
                'reward_benefit_id' => 7,
                'total_hours' =>  168,
                'reward_type' => 'boost',
                'reward_name' => 'Time Freeze',
                'reward_count' => 5
            ]
        );
    }
    
}
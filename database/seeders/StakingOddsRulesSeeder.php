<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StakingOddsRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('staking_odds_rules')->insert(
            [
                'rule' => 'GAME_COUNT_LESS_THAN_5',
                'odds_benefit' => 3,
                'display_name' => 'new_user',
                'odds_operation' => '*'
            ]
        );

        DB::table('staking_odds_rules')->insert(
            [
                'rule' => 'AVERAGE_SCORE_BETWEEN_5_AND_7',
                'odds_benefit' => 1,
                'display_name' => 'average_score_between_5_and_7',
                'odds_operation' => '*'
            ]
        );

        DB::table('staking_odds_rules')->insert(
            [
                'rule' => 'AVERAGE_SCORE_LESS_THAN_5',
                'odds_benefit' => 2.5,
                'display_name' => 'average_score_less_than_5',
                'odds_operation' => '*'
            ]
        );

        DB::table('staking_odds_rules')->insert(
            [
                'rule' => 'AVERAGE_SCORE_GREATER_THAN_7',
                'odds_benefit' => 1,
                'display_name' => 'average_score_greater_than_7',
                'odds_operation' => '*'
            ]
        );

        DB::table('staking_odds_rules')->insert(
            [
                'rule' => 'AT_SPECIAL_HOUR',
                'odds_benefit' => 1.5,
                'display_name' => 'special_hour',
                'odds_operation' => '+'
            ]
        );

        DB::table('staking_odds_rules')->insert(
            [
                'rule' => 'FIRST_TIME_GAME_AFTER_FUNDING',
                'odds_benefit' => 0.5,
                'display_name' => 'funded_wallet',
                'odds_operation' => '+'
            ]
        );
    }
}

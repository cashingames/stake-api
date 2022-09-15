<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OddsConditionsAndRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('odds_conditions_and_rules')->insert(
            [
                'rule' => 'GAME_COUNT_LESS_THAN_5',
                'odds_benefit' => 3,
                'condition' => 'new_user',
            ]
        );

        DB::table('odds_conditions_and_rules')->insert(
            [
                'rule' => 'AVERAGE_SCORE_BETWEEN_5_AND_7',
                'odds_benefit' => 1,
                'condition' => 'average_score_between_5_and_7',
            ]
        );

        DB::table('odds_conditions_and_rules')->insert(
            [
                'rule' => 'AVERAGE_SCORE_LESS_THAN_5',
                'odds_benefit' => 2.5,
                'condition' => 'average_score_less_than_5',
            ]
        );

        DB::table('odds_conditions_and_rules')->insert(
            [
                'rule' => 'AVERAGE_SCORE_GREATER_THAN_7',
                'odds_benefit' => 1,
                'condition' => 'average_score_greater_than_7',
            ]
        );

        DB::table('odds_conditions_and_rules')->insert(
            [
                'rule' => 'AT_SPECIAL_HOUR',
                'odds_benefit' => 1.5,
                'condition' => 'special_hour',
            ]
        );

        DB::table('odds_conditions_and_rules')->insert(
            [
                'rule' => 'FIRST_TIME_GAME_AFTER_FUNDING',
                'odds_benefit' => 0.5,
                'condition' => 'funded_wallet',
            ]
        );
    }
}

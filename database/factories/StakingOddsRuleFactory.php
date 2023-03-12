<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Staking>
 */
class StakingOddsRuleFactory extends Factory
{
    public function definition()
    {
        return [
            'rule' => 'GAME_COUNT_LESS_THAN_5',
            'odds_benefit' => 3,
            'display_name' => 'new_user',
            'odds_operation' => '*'
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContestPrizePool>
 */
class ContestPrizePoolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'rank_from' => 1,
            'rank_to' => $this->faker->randomElement(array(2,3,4,5)),
            'contest_id' =>$this->faker->randomElement(array(1,2,3,4,5)),
            'prize' => $this->faker->word(),
            'prize_type' =>$this->faker->randomElement(array('MONEY_TO_WALLET','POINTS','MONEY_TO_BANK','PHYSICAL_ITEM')),
        ];
    }
}

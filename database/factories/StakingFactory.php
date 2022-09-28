<?php

namespace Database\Factories;

use App\Models\Staking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Staking>
 */
class StakingFactory extends Factory
{   
    protected $model = Staking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
          'user_id' => $this->faker->randomElement(array(1, 2, 3, 4, 5)),
          'amount_staked' => $this->faker->randomElement(array(100, 200, 500, 1000)),
          'standard_odd' => 1
        ];
    }
}

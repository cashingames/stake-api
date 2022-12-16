<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StakingOdd>
 */
class StakingOddFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'score' => fake()->unique()->numberBetween(0, 11),
            'odd' => fake()->unique()->numberBetween(12, 30),
        ];
    }
}

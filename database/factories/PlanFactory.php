<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->numberBetween(100, 1000),
            'game_count' => $this->faker->numberBetween(1, 10),
            'background_color' => $this->faker->hexColor(),
            'is_free' => $this->faker->randomElement(array(true, false)),
        ];
    }
}
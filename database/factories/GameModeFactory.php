<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GameModeFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'display_name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'icon' => $this->faker->imageUrl(),
            'background_color' => $this->faker->hexColor(),
        ];
    }
}
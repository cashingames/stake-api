<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'category_id' => 0,
            'name' => $this->faker->name(),
            'icon' => $this->faker->imageUrl(),
            'description' => $this->faker->sentence(),
            'background_color' => $this->faker->hexColor(),
        ];
    }
}
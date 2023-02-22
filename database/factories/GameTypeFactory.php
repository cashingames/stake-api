<?php

namespace Database\Factories;

use App\Models\GameType;
use Illuminate\Database\Eloquent\Factories\Factory;

class GameTypeFactory extends Factory
{

    protected $model = GameType::class;

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
            'background_color_1' => $this->faker->hexColor(),
            'background_color_2' => $this->faker->hexColor(),
        ];
    }
}
<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promotion>
 */
class PromotionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'description' => $this->faker->text(),
            'title_banner' => $this->faker->imageUrl,
            'description_banner' => $this->faker->imageUrl,
            'start_time' => Carbon::now(),
            'end_time' => Carbon::now()->addHour()
        ];
    }
}

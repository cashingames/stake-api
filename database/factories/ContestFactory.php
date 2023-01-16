<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ContestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'display_name' => $this->faker->word(),
            'entry_mode' => $this->faker->randomElement(array('FREE','PAY_WITH_POINTS','PAY_WITH_MONEY','MINIMUM_POINTS')),
            'contest_type' => $this->faker->randomElement(array('LIVE_TRIVIA', 'LEADERBOARD', 'CHALLENGE')),
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addHour()
        ];
    }
}

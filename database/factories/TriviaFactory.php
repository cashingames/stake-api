<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\GameMode;
use App\Models\GameType;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trivia>
 */
class TriviaFactory extends Factory
{

    public function definition()
    {
        return [
            //
            'name' => $this->faker->word(),
            'category_id' => Category::factory(),
            'game_mode_id' => GameMode::factory(),
            'game_type_id' => GameType::factory(),
            'grand_price' => $this->faker->randomNumber(2),
            'point_eligibility' => $this->faker->randomNumber(2),
            'start_time' => Carbon::now(),
            'end_time' => Carbon::now()->addHour()
        ];
    }
}

<?php

namespace Database\Factories;
use App\Models\LiveTrivia;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trivia>
 */
class LiveTriviaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = LiveTrivia::class;

    public function definition()
    {
        return [
            //
            'name' => $this->faker->word(),
            'category_id' => $this->faker->randomElement(array(501,503,504)),
            'game_mode_id' => 1,
            'game_type_id'=>2,
            'grand_price'=> 1000,
            'point_eligibility'=>500,
            'start_time' => Carbon::now(),
            'end_time' => Carbon::now()->addHour()
        ];
    }
}

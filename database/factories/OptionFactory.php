<?php

namespace Database\Factories;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Lottery;

class OptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Option::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //
            'question_id' => Question::factory(),
            'title' => $this->faker->word(),
            'is_correct' => Lottery::odds(1, 3)
                ->winner(fn() => true)
                ->loser(fn() => false)
        ];
    }
}
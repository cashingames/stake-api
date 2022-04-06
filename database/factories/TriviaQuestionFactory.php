<?php

namespace Database\Factories;
use App\Models\Trivia;
use App\Models\Question;
use App\Models\TriviaQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TriviaQuestion>
 */
class TriviaQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = TriviaQuestion::class; 

    public function definition()
    {
        return [
            //
            'trivia_id' => Trivia::factory(),
            'question_id' => Question::factory()
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\ChallengeRequest;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TriviaChallengeQuestion>
 */
class TriviaChallengeQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'challenge_request_id' => ChallengeRequest::factory(),
            'question_id' => Question::factory(),
            'question_label' => $this->faker->sentence,
            'option_label' => $this->faker->sentence,
        ];
    }
}
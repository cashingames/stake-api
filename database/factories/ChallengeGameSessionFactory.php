<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChallengeGameSession>
 */
class ChallengeGameSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => $this->faker->randomElement(array(1,2,3,4,5)),
            'challenge_id' => $this->faker->randomElement(array(1,2,3,4,5)),
            'game_type_id' => 2,
            'category_id' => $this->faker->randomElement(array(102,502)),
            'start_time' => Carbon::now(),
            'end_time' => Carbon::now()->addMinutes(1),
            'session_token' => Str::random(20),
            'state' => 'COMPLETED',
            'correct_count' => $this->faker->randomElement(array(1,2,3,4,5,6,7,8,9,10)),
            'wrong_count' => $this->faker->randomElement(array(1,2,3,4,5,6,7,8,9,10)),
            'total_count' =>10,
            'points_gained' => $this->faker->randomElement(array(5,10,15,20)),
            'created_at' => Carbon::today()->subDays(2),
            'updated_at' => Carbon::now()
        ];
    }
}

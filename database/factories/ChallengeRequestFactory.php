<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\ChallengeRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChallengeRequest>
 */
class ChallengeRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->hasProfile(1)->hasWallet(1),
            'category_id' => Category::factory(),
            'challenge_request_id' => Str::random(20),
            'username' => $this->faker->userName,
            'amount' => $this->faker->numberBetween(100, 1000),
        ];
    }
}

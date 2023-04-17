<?php

namespace Database\Factories;

use App\Models\Boost;
use App\Models\ChallengeRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChallengeBoost>
 */
class ChallengeBoostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'challenge_request_id' => ChallengeRequest::factory()->create()->challenge_request_id,
            'game_session_id' => Boost::factory(),
        ];
    }
}

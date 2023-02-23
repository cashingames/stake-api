<?php

namespace Database\Factories;

use App\Models\GameSession;
use App\Models\Staking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExhibitionStaking>
 */
class ExhibitionStakingFactory extends Factory
{

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition()
  {
    return [
      'staking_id' => Staking::factory(),
      'game_session_id' => GameSession::factory(),
      'odds_applied' => $this->faker->randomElement(array(1, 2, 3, 4, 5)),
    ];
  }
}
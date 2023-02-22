<?php

namespace Database\Factories;

use App\Models\ExhibitionStaking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExhibitionStaking>
 */
class ExhibitionStakingFactory extends Factory
{
  protected $model = ExhibitionStaking::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition()
  {
    return [
      'staking_id' => $this->faker->randomElement(array(1, 2, 3, 4, 5)),
      'game_session_id' => $this->faker->randomElement(array(100, 200, 500, 1000)),
      'odds_applied' => $this->faker->randomElement(array(1, 2, 3, 4, 5)),
    ];
  }
}
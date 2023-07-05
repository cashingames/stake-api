<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Staking>
 */
class StakingFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition()
  {
    return [
      'user_id' => User::factory(),
      'amount_staked' => $this->faker->randomNumber(2),
      'odd_applied_during_staking' => 1,
      'amount_won' => $this->faker->randomNumber(2),
      'fund_source' => 'CREDIT',
    ];
  }
}
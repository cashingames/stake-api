<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\GameType;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
  protected $model = Question::class;

  /**
   * Define the model's default state.
   *
   * @return array
   */
  public function definition()
  {
    return [
      'label' => $this->faker->sentence(5),
      'level' => $this->faker->randomElement(array('easy', 'medium', 'hard')),
      'game_type_id' => function () {
        return GameType::inRandomOrder()->first()->id;
      },
      'is_published' => true
    ];
  }
}

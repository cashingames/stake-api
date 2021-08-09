<?php

namespace Database\Factories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
      'level' => $this->faker->randomElement(array('easy', 'medium', 'hard') ),
      'game_type_id' => $this->faker->randomElement(array(1,2)),
      'category_id' => $this->faker->randomElement(array(1,2,3,4,5,6,7,8,9,10,11))
    ];
  }


}

// $factory->define(Question::class, function (Faker $faker) {
//     return [
//         'label'=>$faker->sentence(5),
//         'level'=>$faker->randomElement(array('easy', 'medium', 'hard') ),
//         'category_id'=>$faker->randomElement(array(2,3))
//     ];
// });

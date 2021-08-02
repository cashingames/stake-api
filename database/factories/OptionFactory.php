<?php

namespace Database\Factories;

use App\Models\Option;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OptionFactory extends Factory
{
  protected $model = Option::class;

  /**
   * Define the model's default state.
   *
   * @return array
   */
  public function definition()
  {
    return [
      'title'=> $this->faker->sentence(3),
      'is_correct' => false,
    ];
  }

}
// $factory->define(Option::class, function (Faker $faker) {
//     return [
//         'title'=> $faker->sentence(3),
//         'is_correct' => false,
//     ];
// });

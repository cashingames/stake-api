<?php

namespace Database\Factories;

use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;


class VoucherFactory extends Factory
{
  protected $model = Voucher::class;

  /**
   * Define the model's default state.
   *
   * @return array
   */
  public function definition()
  {
    return [
      'code' => Str::random(10),
      'expire' => now()->addDays(1),
      'count' => $this->faker->randomDigitNotNull(),
      'unit' => $this->faker->randomFloat(2, 150, 10000),
      'type'=> $this->faker->randomElement(array('cash', 'cash') ),
    ];
  }

}

// $factory->define(App\Voucher::class, function (Faker $faker) {
//     return [
//         'code' => Str::random(10),
//         'expire' => now()->addDays(1),
//         'count' => $faker->randomDigitNotNull(),
//         'unit' => $faker->randomFloat(2, 150, 10000),
//         'type'=>$faker->randomElement(array('cash', 'cash') ),
//     ];
// });

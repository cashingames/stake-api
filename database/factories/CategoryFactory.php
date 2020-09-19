<?php
namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
  
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
      return [
        'name' => $this->faker->name,
        'description' => $this->faker->text,
        'instruction' => $this->faker->text,
      ];
    }

}

// $factory->define(Category::class, function (Faker $faker) {
//     return [
//         'name' => $faker->name,
//         'description' => $faker->text,
//         'instruction' => $faker->text,
//     ];
// });

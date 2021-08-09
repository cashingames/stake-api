<?php

namespace Database\Factories;

use App\Models\UserQuiz;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserQuizFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserQuiz::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //
            'user_id' => User::factory(),
            'category_id' => $this->faker->randomElement(array(5,6)),
            'title' => $this->faker->word(),
            'description' => $this->faker->sentence(),
        ];
    }
}

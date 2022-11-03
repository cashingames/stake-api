<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Challenge>
 */
class ChallengeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => $this->faker->randomElement(array(1, 2, 3, 4, 5)),
            'opponent_id' => function (array $attributes) {
                $ids = array(1, 2, 3, 4, 5);
                return $this->faker->randomElement(array_diff($ids, [$attributes['user_id']]));

            },
            'category_id' => $this->faker->randomElement(array(501, 503, 504)),
            'status' => $this->faker->randomElement(array('PENDING', 'ACCEPTED', 'DECLINED')),
        ];
    }
}

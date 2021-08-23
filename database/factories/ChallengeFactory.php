<?php

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChallengeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Challenge::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //

            'user_id' => $this->faker->randomElement(array(1,2,3,4,5) ),
            'opponent_id' => $this->faker->randomElement(array(1,2,3,4,5) ),
            'category_id' =>$this->faker->randomElement(array(1,2,3,4,5,6,7,8,9,10,11) ),
            'game_type_id' =>$this->faker->randomElement(array(1,2) ),
            'status' => $this->faker->randomElement(array("ACCEPTED", "PENDING", "DECLINED") ),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\UserPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\User;

class UserPlanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserPlan::class;

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
            'plan_id' => $this->faker->randomElement(array(1,2,3,4)),
            'is_active'=> true
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Plan;
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
            'plan_id' => Plan::factory(),
            'is_active'=> true
        ];
    }
}

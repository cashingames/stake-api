<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{

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
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            // 'phone_number' => $this->faker->phoneNumber,
            'avatar' => $this->faker->imageUrl,
            'bank_name' => $this->faker->company,
            'account_number' => $this->faker->iban('ng'),
            'account_name' => $this->faker->name,
            'referral_code' => function (array $attributes) {
                return User::find($attributes['user_id'])->username;
            },
            // 'referrer' => function (array $attributes) {
            //     return User::find($attributes['user_id'])->type;
            // },
        ];
    }
}

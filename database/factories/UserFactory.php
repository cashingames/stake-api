<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'username' => $this->faker->username . rand(1, 99),
            'email' => $this->faker->unique()->safeEmail . "" . rand(10, 999),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'otp_token'=> mt_rand(10000,99999) . "" . rand(10, 999),
            'is_on_line' =>$this->faker->randomElement(array(true, false) ),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'is_a_bot' => $this->faker->randomElement(array(true , false)),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
                'phone_verified_at' => null
            ];
        });
    }
}

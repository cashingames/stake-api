<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\RealtimeChallengeRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class RealTimeChallengeRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = RealtimeChallengeRequest::class;

    public function definition(): array
    {
        return [
            'user_id' => function () {
                return User::inRandomOrder()->first()->id;
            },
            'category_id' => function () {
                return Category::inRandomOrder()->first()->id;
            },
            'document_id' => Str::random(10),
            'amount' => $this->faker->numberBetween(100, 1000),
        ];
    }
}

<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Option;
use Faker\Generator as Faker;

$factory->define(Option::class, function (Faker $faker) {
    return [
        'title'=> $faker->sentence(3),
        'is_correct' => false,
    ];
});

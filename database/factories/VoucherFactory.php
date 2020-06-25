<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(App\Voucher::class, function (Faker $faker) {
    return [
        'code' => Str::random(10),
        'expire' => now()->addDays(1),
        'count' => $faker->randomDigitNotNull(),
        'unit' => $faker->randomFloat(2, 150, 10000),
        'type' => 'cash, live',
    ];
});

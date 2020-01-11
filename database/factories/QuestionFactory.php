<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Question;
use Faker\Generator as Faker;

$factory->define(Question::class, function (Faker $faker) {
    return [
        'label'=>$faker->sentence(5),
        'level'=>$faker->randomElement(array('easy', 'medium', 'hard') ),
        'category_id'=>$faker->randomElement(array(2,3))
    ];
});

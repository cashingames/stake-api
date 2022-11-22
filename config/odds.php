<?php

return [
    'enabled' => true,
    'standard' => array(
        10 => 10,
        9 => 5,
        8 => 3,
        7 => 1,
        6 => 0.8,
        5 => 0.5,
        4 => 0.4,
        3 => 0.3,
        2 => 0.2,
        1 => 0.1,
        0 => 0,
    ),
    'special_hours' => [
        '12:00',
        '18:00',
        '21:00',
        '0:00',
    ],
    'minimum_exhibition_staking_amount' => env('MINIMUM_EXHIBITION_STAKING_AMOUNT', 100),
    'maximum_exhibition_staking_amount' => env('MAXIMUM_EXHIBITION_STAKING_AMOUNT', 1000),
    'minimum_challenge_staking_amount' => env('MINIMUM_CHALLENGE_STAKING_AMOUNT', 100),
    'maximum_challenge_staking_amount' => env('MAXIMUM_CHALLENGE_STAKING_AMOUNT', 1000000),
    'minimum_live_trivia_staking_amount' => env('MINIMUM_LIVE_TRIVIA_STAKING_AMOUNT', 100),
    'maximum_live_trivia_staking_amount' => env('MAXIMUM_LIVE_TRIVIA_STAKING_AMOUNT', 1000)
];
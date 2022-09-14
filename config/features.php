<?php

return [
    'odds' => [
        'enabled' => env('ODDS_ENABLED', false),
        'activate_on' => null //@TODO future consideration: making a feature activated beginning from a certain date
    ],
    'question_hardening' => [
        'enabled' => env('QUESTION_HARDENER_ENABLED', false),
        'activate_on' => null
    ],
    'phone_verification' => [
        'enabled' => env('PHONE_VERIFICATION_ENABLED', false),
        'activate_on' => null
    ],
    'game_staking' => [
        'enabled' => env('GAME_STAKING_ENABLED', false),
        'activate_on' => null
    ],
    'notification_history' => [
        'enabled' => env('NOTIFICATION_HISTORY_ENABLED', false),
        'activate_on' => '2022-09-13'
    ]
];
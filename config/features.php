<?php

return [
    'odds' => [
        'enabled' => env('FEATURE_FLAG_ODDS_ENABLED', false),
        'activate_on' => null //@TODO future consideration: making a feature activated beginning from a certain date
    ],
    'phone_verification' => [
        'enabled' => env('FEATURE_FLAG_PHONE_VERIFICATION_ENABLED', false),
        'activate_on' => null
    ],
    'game_staking' => [
        'enabled' => env('FEATURE_FLAG_GAME_STAKING_ENABLED', false),
        'activate_on' => null
    ],
    'notification_history' => [
        'enabled' => env('FEATURE_FLAG_NOTIFICATION_HISTORY_ENABLED', false),
        'activate_on' => '2022-09-13'
    ]
];
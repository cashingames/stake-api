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
    'exhibition_game_staking' => [
        'enabled' => env('FEATURE_FLAG_EXHIBITION_GAME_STAKING_ENABLED', false),
        'activate_on' => null
    ],
    'trivia_game_staking' => [
        'enabled' => env('FEATURE_FLAG_TRIVIA_GAME_STAKING_ENABLED', false),
        'activate_on' => null
    ],
    'notification_history' => [
        'enabled' => env('FEATURE_FLAG_NOTIFICATION_HISTORY_ENABLED', false),
        'activate_on' => '2022-09-13'
    ],
    'achievement_badges' => [
        'enabled' => env('FEATURE_FLAG_ACHIEVEMENT_BADGES_ENABLED', false),
        'activate_on' => null
    ],
    'special_hour_notification' => [
        'enabled' => env('FEATURE_FLAG_SPECIAL_HOUR_NOTIFICATION_ENABLED', false),
        'activate_on' => null
    ],
    'email_verification' => [
        'enabled' => env('FEATURE_FLAG_EMAIL_VERIFICATION_ENABLED', false),
        'activate_on' => null
    ],
    'live_trivia_start_time_notification' => [
        'enabled' => env('FEATURE_FLAG_LIVE_TRIVIA_START_TIME_NOTIFICATION_ENABLED', false),
        'activate_on' => null
    ],
];
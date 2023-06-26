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
    'notification_history' => [
        'enabled' => env('FEATURE_FLAG_NOTIFICATION_HISTORY_ENABLED', false),
        'activate_on' => '2022-09-13'
    ],
    'email_verification' => [
        'enabled' => env('FEATURE_FLAG_EMAIL_VERIFICATION_ENABLED', false),
        'activate_on' => null
    ],
    'withdrawable_wallet' => [
        'enabled' => env('FEATURE_FLAG_WITHDRAWABLE_WALLET_ENABLED', false),
        'activate_on' => null
    ],
    'staking_with_odds' => [
        'enabled' => env('FEATURE_FLAG_STAKING_WITH_ODDS_ENABLED', false),
        'activate_on' => null
    ],
    'in_app_activities_push_notification' => [
        'enabled' => env('FEATURE_FLAG_IN_APP_ACTIVITIES_PUSH_NOTIFICATION_ENABLED', false),
        'activate_on' => null
    ],
    'send_automated_reports' => [
        'enabled' => env('FEATURE_FLAG_SEND_AUTOMATED_REPORTS_ENABLED', false),
        'activate_on' => null
    ],
    'registration_bonus' => [
        'enabled' => env('FEATURE_FLAG_BONUS_ON_REGISTRATION_ENABLED', false),
        'activate_on' => null
    ],
];

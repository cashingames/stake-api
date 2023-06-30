<?php

return [
    'bonus' => [
        'signup' => [
            'stakers_bonus_amount' => env('STAKERS_SIGNUP_BONUS_AMOUNT', 0),
            'registration_bonus_limit' => env('REGISTRATION_BONUS_LIMIT', 10000),
            'registration_bonus_percentage' => env('REGISTRATION_BONUS_PERCENTAGE', 100)
        ],
    ],
    'wallet_funding' => [
        'min_amount' => env('MINIMUM_WALLET_FUNDABLE_AMOUNT', 100),
        'max_amount' => env('MAXIMUM_WALLET_FUNDABLE_AMOUNT', 100000)
    ],
    'user_scores' => [
        'perfect_score' => env('PERFECT_SCORE', 10),
        'high_score' => env('HIGH_SCORE', 8),
        'medium_score' => env('MEDIUM_SCORE', 5),
        'low_score' => env('LOW_SCORE', 2)
    ],
    'platform_target' => env('PLATFORM_TARGET_PERCENTAGE', 50),
    'min_version_code' => env('MIN_CODE_VERSION', '1.0.35'),
    'minimum_game_boost_score' => env("MINIMUM_GAME_BOOST_SCORE", 4),
    'min_version_force' => env('MIN_VERSION_FORCE', false),
    'payment_key' => env('PAYSTACK_KEY', null),
    'min_withdrawal_amount' => env('MINIMUM_WITHDRAWAL_AMOUNT', 500),
    'max_withdrawal_amount' => env('MAXIMUM_WITHDRAWAL_AMOUNT', 10000),
    'hours_before_withdrawal' => env('HOURS_BEFORE_WITHDRAWAL', 3),
    'total_withdrawal_days_limit' => env('TOTAL_WITHDRAWAL_DAYS_LIMIT', 7),
    'total_withdrawal_limit' => env('TOTAL_WITHDRAWAL_LIMIT', 20000),
    'minimum_exhibition_staking_amount' => env('MINIMUM_EXHIBITION_STAKING_AMOUNT', 100),
    'maximum_exhibition_staking_amount' => env('MAXIMUM_EXHIBITION_STAKING_AMOUNT', 1000),
    'minimum_challenge_staking_amount' => env('MINIMUM_CHALLENGE_STAKING_AMOUNT', 100),
    'maximum_challenge_staking_amount' => env('MAXIMUM_CHALLENGE_STAKING_AMOUNT', 1000000),
    'email_verification_limit_threshold' => env('EMAIL_VERIFICATION_LIMIT_THRESHOLD', 1000),
    'minimum_withdrawal_perfect_score_threshold' => env('MINIMUM_WITHDRAWAL_PERFECT_SCORE_THRESHOLD', 5)
];

<?php

return [
    'bonus' => [
        'enabled' => env('BONUS', false),
        'signup' => [
            'enabled' => env('SIGNUP_BONUS', false),
            'stakers_bonus_amount' => env('STAKERS_SIGNUP_BONUS_AMOUNT', 0),
            'general_bonus_amount' => env('SIGNUP_BONUS_AMOUNT', 0),
            'referral' => env('REFERRER_SIGNUP_BONUS', false),
            'referral_on_first_game' => env('REFERRER_SIGNUP_BONUS_ON_FIRST_GAME', false),
            'referral_on_signup' => env('REFERRER_SIGNUP_BONUS_ON_REGISTRATION', false),
            'referral_on_signup_bonus_amount' => env('REFERRER_SIGNUP_BONUS_GAMES_COUNT', 2),
            'registration_bonus_limit' => env('REGISTRATION_BONUS_LIMIT', 10000),
            'registration_bonus_percentage' => env('REGISTRATION_BONUS_PERCENTAGE', 100)
        ],
    ],
    'tournament' => [
        'enabled' => env('IS_ON_TOURNAMENT', false),
        'start_time' => env('TOURNAMENT_START_TIME', "00:00:00"),
        'end_time' => env('TOURNAMENT_END_TIME', "00:00:00"),
        'categories' => [env('TOURNAMENT_CATEGORY1', ''), env('TOURNAMENT_CATEGORY2', '')],
    ],
    'game' => [
        'questions_count' => env('GAME_QUESTIONS_COUNT', 10),
        'demo_games_count' => env('DEMO_GAMES_COUNT', 5)
    ],
    'live_trivia' => [
        'enabled' => env('HAS_LIVE_TRIVIA', false),
        'display_shelf_life' => env('LIVE_TRIVIA_DISPLAY_SHELF_LIFE', 1)
    ],
    'wallet_funding' => [
        'min_amount' => env('MINIMUM_WALLET_FUNDABLE_AMOUNT', 100),
        'max_amount' => env('MAXIMUM_WALLET_FUNDABLE_AMOUNT', 100000)
    ],
    'coin_reward' => [
        'user_scores' => [
            'perfect_score' => env('PERFECT_SCORE', 10),
            'high_score' => env('HIGH_SCORE', 8),
            'medium_score' => env('MEDIUM_SCORE', 5),
            'low_score' => env('LOW_SCORE', 2)
        ],
        'coins_earned' => [
            'perfect_coin' => env('PERFECT_COIN', 30),
            'high_coin' => env('HIGH_COIN', 20),
            'medium_coin' => env('MEDIUM_COIN', 10),
            'low_coin' => env('LOW_COIN', 5)
        ]
    ],
    'platform_target' => env('PLATFORM_TARGET_PERCENTAGE', 50),
    'min_version_code' => env('MIN_CODE_VERSION', '1.0.35'),
    'minimum_game_boost_score' => env("MINIMUM_GAME_BOOST_SCORE", 4),
    'min_version_force' => env('MIN_VERSION_FORCE', false),
    'min_version_code_gameark' => env('MIN_CODE_VERSION_GAMEARK', '1.1.11'),
    'min_version_force_gameark' => env('MIN_VERSION_FORCE_GAMEARK', false),
    'payment_key' => env('PAYSTACK_KEY', null),
    'use_lite_client' => env('USE_LITE_FRONTEND', true),
    'admin_withdrawal_request_email' => env('ADMIN_MAIL_ADDRESS', 'hello@cashingames.com'),
    'challenge_staking_platform_charge_percent' => env('CHALLENGE_STAKING_PLATFORM_CHARGE_PERCENT', 0),
    'duration_hours_before_challenge_staking_expiry' => env('DURATION_HOURS_BEFORE_CHALLENGE_STAKING_EXPIRY', 48),
    'min_withdrawal_amount' => env('MINIMUM_WITHDRAWAL_AMOUNT', 500),
    'max_withdrawal_amount' => env('MAXIMUM_WITHDRAWAL_AMOUNT', 10000),
    'hours_before_withdrawal' => env('HOURS_BEFORE_WITHDRAWAL', 3),
    'total_withdrawal_days_limit' => env('TOTAL_WITHDRAWAL_DAYS_LIMIT', 7),
    'total_withdrawal_limit' => env('TOTAL_WITHDRAWAL_LIMIT', 20000),
    'minimum_exhibition_staking_amount' => env('MINIMUM_EXHIBITION_STAKING_AMOUNT', 100),
    'maximum_exhibition_staking_amount' => env('MAXIMUM_EXHIBITION_STAKING_AMOUNT', 1000),
    'minimum_challenge_staking_amount' => env('MINIMUM_CHALLENGE_STAKING_AMOUNT', 100),
    'maximum_challenge_staking_amount' => env('MAXIMUM_CHALLENGE_STAKING_AMOUNT', 1000000),
    'minimum_live_trivia_staking_amount' => env('MINIMUM_LIVE_TRIVIA_STAKING_AMOUNT', 100),
    'maximum_live_trivia_staking_amount' => env('MAXIMUM_LIVE_TRIVIA_STAKING_AMOUNT', 1000),
    'email_verification_limit_threshold' => env('EMAIL_VERIFICATION_LIMIT_THRESHOLD',1000)
];

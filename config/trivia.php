<?php

return [
  'bonus' => [
    'enabled' => env('BONUS', false),
    'signup' => [
      'enabled' => env('SIGNUP_BONUS', false),
      'amount' => env('SIGNUP_BONUS_AMOUNT', 0),
      'referral' => env('REFERRER_SIGNUP_BONUS', false),
      'referral_on_first_game' => env('REFERRER_SIGNUP_BONUS_ON_FIRST_GAME', false),
      'referral_on_signup' => env('REFERRER_SIGNUP_BONUS_ON_REGISTRATION', false),
    ],
  ],
  'tournament' => [
    'enabled' => env('IS_ON_TOURNAMENT', false),
    'start_time' => env('TOURNAMENT_START_TIME',"00:00:00"),
    'end_time' => env('TOURNAMENT_END_TIME', "00:00:00"),
    'categories' => [env('TOURNAMENT_CATEGORY1',''), env('TOURNAMENT_CATEGORY2', '')],
  ],
  'product_launch'=>[
    'is_launching'=>env('IS_LAUNCHING', false),
    'categories'=> [env('LAUNCH_CATEGORY1',''), env('LAUNCH_CATEGORY2', '')],
  ],
  'game'=>[
    'questions_count'=>env('GAME_QUESTIONS_COUNT',10),
  ],
  'live_trivia'=>[
    'enabled' => env('HAS_LIVE_TRIVIA',false)
  ],
  'min_version_code' => env('MIN_CODE_VERSION', '1.0.35'),
  'min_version_force' => env('MIN_VERSION_FORCE', false),
  'payment_key' => env('PAYSTACK_KEY', null),
  'use_lite_client' => env('USE_LITE_FRONTEND', true),
  'admin_withdrawal_request_email'=>env('ADMIN_MAIL_ADDRESS','hello@cashingames.com' ),
];
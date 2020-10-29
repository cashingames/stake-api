<?php

return [
  'bonus' => [
    'enabled' => env('BONUS', false),
    'signup' => [
      'enabled' => env('SIGNUP_BONUS', false),
      'amount' => env('SIGNUP_BONUS_AMOUNT', 0),
      'referral' => env('REFERRER_SIGNUP_BONUS', false),
      'referral_amount' => env('REFERRER_SIGNUP_BONUS_AMOUNT', 0),
      'referral_on_first_game' => env('REFERRER_SIGNUP_BONUS_ON_FIRST_GAME', false),
      'referral_on_signup' => env('REFERRER_SIGNUP_BONUS_ON_REGISTRATION', false),

    ],
  ],
  'payment_key' => env('PAYSTACK_KEY', null),
  'use_lite_client' => env('USE_LITE_FRONTEND', true),
  'set_claims_active' => env('SET_CLAIMS_ACTIVE', true),
  'admin_withdrawal_request_email'=>env('ADMIN_WITHDRAWAL_REQUEST_EMAIL','hello@cashingames.com' ),
];
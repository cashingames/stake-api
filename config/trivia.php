<?php

return [
  'bonus' => [
    'enabled' => env('BONUS', false),
    'signup' => [
      'enabled' => env('SIGNUP_BONUS', false),
      'amount' => env('SIGNUP_BONUS_AMOUNT', 0),
      'referral' => env('REFERRER_SIGNUP_BONUS', false),
      'referral_amount' => env('REFERRER_SIGNUP_BONUS_AMOUNT', 0),
    ],
  ],
];
<?php

return [
   
    'phone_verification' => [
        'enabled' => env('FEATURE_FLAG_PHONE_VERIFICATION_ENABLED', false),
        'activate_on' => null
    ],
   
    'email_verification' => [
        'enabled' => env('FEATURE_FLAG_EMAIL_VERIFICATION_ENABLED', false),
        'activate_on' => null
    ],
 
    'registration_bonus' => [
        'enabled' => env('FEATURE_FLAG_BONUS_ON_REGISTRATION_ENABLED', false),
        'activate_on' => null
    ],
];

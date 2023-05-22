<?php

namespace App\Enums;

enum AuthTokenType: string
{
    case PhoneVerification = "PHONE_VERIFICATION";
    case EmailVerification = "EMAIL_VERIFICATION";
    case PasswordReset = "PASSWORD_RESET";
}

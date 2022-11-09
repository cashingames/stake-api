<?php

namespace App\Enums;

enum PushNotificationType: string
{
    case Challenge = "CHALLENGE";
    case Wallet = "WALLET";
    case ActivityUpdate = "ACTIVITY_UPDATE";
}

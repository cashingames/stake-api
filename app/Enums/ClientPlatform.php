<?php

namespace App\Enums;

use Illuminate\Support\Arr;

enum ClientPlatform
{
    case StakingMobileWeb;
    case StakingMobileApp;
    case CashingamesMobile;
    case CashingamesWeb;

    public static function detect($payload): self
    {
        if (!Arr::has($payload, 'x-brand-id')) {
            return self::CashingamesMobile;
        }

        switch ($payload['x-brand-id']) {
            case 2:
                return self::StakingMobileWeb;
            case 1 || 3:
            default:
                return self::CashingamesMobile;
        }

    }
}
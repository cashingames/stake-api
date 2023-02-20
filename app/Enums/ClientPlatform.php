<?php

namespace App\Enums;

use Illuminate\Support\Arr;

enum ClientPlatform
{
    case StakingMobileWeb;
    case StakingMobileApp;
    case CashingamesMobile;
    case CashingamesWeb;

    public static function detect(int $brandId): self
    {
        switch ($brandId) {
            case 2:
                return self::StakingMobileWeb;
            case 1 || 3:
            default:
                return self::CashingamesMobile;
        }

    }
}
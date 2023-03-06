<?php

namespace App\Enums;

use Illuminate\Support\Arr;

enum ClientPlatform
{
    case StakingMobileWeb;
    case StakingMobileApp;
    case CashingamesMobile;
    case CashingamesWeb;
    case GameArkMobile;

    public static function detect($brandId): self
    {
        switch ($brandId) {
            case 2:
                return self::StakingMobileWeb;
            case 10:
                return self::GameArkMobile;
            case 1 || 3:
            default:
                return self::CashingamesMobile;
        }

    }
}

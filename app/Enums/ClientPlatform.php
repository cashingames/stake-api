<?php

namespace App\Enums;

enum ClientPlatform
{

    case MobileAndroid;

    case MobileIOS;

    case MobileWebAndroid;

    case MobileWebIOS;

    case MobileWebMac;

    case MobileWebWindows;

    public static function detect($brandId): self
    {
        switch ($brandId) {
            case 1:
                return self::MobileAndroid;
            case 2: //works for mobile and mobile web
                return self::MobileIOS;
            case 3:
                return self::MobileWebAndroid;
            case 4:
                return self::MobileWebIOS;
            case 5:
                return self::MobileWebMac;
            case 6:
                return self::MobileWebWindows;
            default:
                return self::MobileAndroid;
        }

    }
}

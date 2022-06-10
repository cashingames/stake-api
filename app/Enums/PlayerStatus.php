<?php

namespace App\Enums;

enum PlayerStatus : string {
    case Played = "PLAYED";
    case InsufficientPoints = "INSUFFICIENTPOINTS";
    case CanPlay = "CANPLAY";
}

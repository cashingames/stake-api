<?php

namespace App\Enums;

enum LiveTriviaPlayerStatus : string {
    case Played = "PLAYED";
    case LowPoints = "INSUFFICIENTPOINTS";
    case CanPlay = "CANPLAY";
}

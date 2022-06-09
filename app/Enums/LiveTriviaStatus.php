<?php

namespace App\Enums;

enum LiveTriviaStatus : string {
    case Waiting = "WAITING";
    case Ongoing = "ONGOING";
    case Closed = "CLOSED";
    case Expired = "EXPIRED";
}

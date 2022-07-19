<?php

namespace App\Enums;

enum ChallengeStatus: string {
    case Ongoing = "ONGOING";
    case Closed = "CLOSED";
    case Pending = "PENDING";
}

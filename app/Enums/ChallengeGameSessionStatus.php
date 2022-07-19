<?php

namespace App\Enums;

enum ChallengeGameSessionStatus: string {
    case Ongoing = "ONGOING";
    case Closed = "CLOSED";
    case Pending = "PENDING";
}

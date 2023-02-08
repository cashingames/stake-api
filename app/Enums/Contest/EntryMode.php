<?php

namespace App\Enums\Contest;

enum EntryMode: string
{
    case free = "FREE";
    case PayWithPoints = "PAY_WITH_POINTS";
    case PayWithMoney = "PAY_WITH_MONEY";
    case MinimumPointsDaily = "MINIMUM_POINTS_DAILY";
    case MinimumPointsWeekly = "MINIMUM_POINTS_WEEKLY";
    case MinimumPointsMonthly = "MINIMUM_POINTS_MONTHLY";
}

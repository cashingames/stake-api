<?php

namespace App\Enums;

enum StakingFundSource: string
{
    case Credit = "CREDIT";
    case Bonus = "BONUS";
    case Winnings = "WINNINGS";
}
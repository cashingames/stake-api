<?php

namespace App\Enums;

enum StakingFundSource: string
{
    case DEPOSIT = "DEPOSIT";
    case BONUS = "BONUS";
    case WINNING = "WINNING";
}
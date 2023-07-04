<?php

namespace App\Enums;

enum BonusTriggers: string
{
    case FirstTimeFunding = "FIRST_TIME_FUNDING";
    case LossOnStaking = "LOSS_ON_STAKING";
}

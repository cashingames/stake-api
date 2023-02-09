<?php

namespace App\Enums\Contest;

enum PrizeType: string
{
    case Points = "POINTS";
    case MoneyToWallet = "MONEY_TO_WALLET";
    case MoneyToBank = "MONEY_TO_BANK";
    case PhysicalItem = "PHYSICAL_ITEM";
}

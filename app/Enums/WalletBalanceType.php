<?php

namespace App\Enums;

enum WalletBalanceType: string {
    case CreditsBalance = "CREDIT_BALANCE";
    case BonusBalance = "BONUS_BALANCE";
    case WinningsBalance = "WINNINGS_BALANCE";
}

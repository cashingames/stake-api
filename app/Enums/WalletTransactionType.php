<?php

namespace App\Enums;

enum WalletTransactionType: string {
    case Credit = "CREDIT";
    case Debit = "DEBIT";
}

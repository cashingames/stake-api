<?php

namespace App\Enums;

enum WalletTransactionAction: string {
    case BoostBought = "BOOST_BOUGHT";
    case GamesBought = "GAMES_BOUGHT";
    case WalletFunded = "WALLET_FUNDED";
    case WinningsCredited = "WINNINGS_CREDITED";
    case WinningsWithdrawn = "WINNINGS_WITHDRAWN";
    case StakingPlaced = "STAKING_PLACED";
    case BonusCredited = "BONUS_CREDITED";
    case LiveTriviaFeePaid = "LIVE_TRIVIA_FEE_PAID";
    case FundsReversed = "FUNDS_REVERSED";
    case BonusTurnoverMigrated = "BONUS_TURNOVER_MIGRATED";
}

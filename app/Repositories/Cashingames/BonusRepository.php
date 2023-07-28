<?php

namespace App\Repositories\Cashingames;

use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Models\WalletTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BonusRepository
{
    public function getUsersLossBetween(Carbon $startDate, Carbon $endDate)
    {
        $usersWithLosses = DB::select("
            SELECT
                wallets.id as wallet_id,
                wallets.bonus as bonus_balance,
                stakings.user_id,
                sum(amount_won) - sum(amount_staked) as win,
                ABS(sum(amount_won) - sum(amount_staked)) loss
            FROM
                stakings
            LEFT JOIN
                users on users.id = stakings.user_id
            LEFT JOIN
                wallets on wallets.user_id = users.id
            WHERE
                (DATE(stakings.created_at) BETWEEN '{$startDate->toDateString()}' AND '{$endDate->toDateString()}')
            GROUP BY
                stakings.user_id
            HAVING
                win < 0
            
        ");

        return collect($usersWithLosses);
    }

    public function giveCashback($usersWithLosses, $cashbackPercentage = 10)
    {
        DB::transaction(function () use ($usersWithLosses, $cashbackPercentage) {
            $usersWithLosses->each(function ($data) use ($cashbackPercentage) {
                $actualCashback = $data->loss * $cashbackPercentage / 100;
                $bonusBalance = $data->bonus_balance + $actualCashback;
                $this->updateBonusBalance($data->wallet_id, $bonusBalance, $actualCashback);
            });
        });
    }

    private function updateBonusBalance($walletId, $bonusBalance, $amount): void
    {
        DB::update("
            UPDATE
                wallets
            SET
                bonus = bonus + {$amount}
            WHERE
                id = {$walletId}
        ");

        WalletTransaction::create([
            'wallet_id' => $walletId,
            'transaction_type' => 'CREDIT',
            'amount' => $amount,
            'balance' => $bonusBalance,
            'description' => 'Casback Credited',
            'reference' => Str::random(10),
            'balance_type' => WalletBalanceType::BonusBalance->value,
            'transaction_action' => WalletTransactionAction::BonusCredited->value
        ]);
    }

}
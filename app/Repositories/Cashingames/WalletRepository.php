<?php

namespace App\Repositories\Cashingames;

use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Models\Staking;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletRepository
{

    /**
     * To calculate the percentage profit, you need to calculate the difference between the amount received
     * and the initial stake, and then divide by the initial stake and multiply by 100.
     * e.g I staked with 100 and got 15 back how much did I profit in percentage
     * In this case, the amount received was 15 and the initial stake was 100. So the profit would be:
     * (15 – 100) / 100 = -85%
     * Note that the result is negative, which means that there was a loss rather than a profit.
     *
     * If the amount received was greater than the initial stake, the result would be positive.
     * e.g I staked with 100 and got 150 back how much did I profit in percentage
     * In this case, the amount received was 150 and the initial stake was 100. So the profit would be:
     * (150 – 100) / 100 = 50%
     * Note that the result is positive, which means that there was a profit rather than a loss.
     *
     * @TODO How can we determine this from wallet or wallet transactions? so that we are game type agnostic
     *
     * @param mixed $user
     * @return float
     */
    public function getUserProfitPercentageOnStaking(int $userId, Carbon $startDate, Carbon $endDate): int|float
    {
        $todayStakes = Staking::selectRaw('sum(amount_staked) as amount_staked, sum(amount_won) as amount_won')
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->first();
        $amountStaked = $todayStakes?->amount_staked ?? 0;
        $amountWon = $todayStakes?->amount_won ?? 0;

        if ($amountStaked == 0) {
            return 0;
        }

        return (($amountWon - $amountStaked) / $amountStaked) * 100;
    }


    /**
     * Platform profit is the opposite of total users profit
     * e,g if users profit is 10%, then platform profit is -10%
     *
     * @return float|int
     */
    public function getPlatformProfitPercentageOnStaking(Carbon $startDate, Carbon $endDate): int|float
    {
        $todayStakes = Staking::selectRaw('sum(amount_staked) as amount_staked, sum(amount_won) as amount_won')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->first();
        $amountStaked = $todayStakes?->amount_staked ?? 0;
        $amountWon = $todayStakes?->amount_won ?? 0;


        /**
         * If no stakes were made today, then the platform is neutral
         * So first user should be lucky
         */

        if ($amountStaked == 0) {
            return 0;
        }

        return (($amountWon - $amountStaked) / $amountStaked) * -100;
    }


    /**
     * Helper methods
     * @section
     */

    // get user profit on staking today
    public function getUserProfitPercentageOnStakingToday(int $userId): int|float
    {
        return $this->getUserProfitPercentageOnStaking($userId, now()->startOfDay(), now()->endOfDay());
    }

    public function getUserProfitPercentageOnStakingThisYear(int $userId): int|float
    {
        return $this->getUserProfitPercentageOnStaking($userId, now()->startOfYear(), now());
    }

    //get platform profit on staking today
    public function getPlatformProfitPercentageOnStakingToday(): int|float
    {
        return $this->getPlatformProfitPercentageOnStaking(now()->startOfDay(), now()->endOfDay());
    }

    public function credit(Wallet $wallet, float $amount, string $description, string $reference = null): void
    {
        DB::transaction(function () use ($wallet, $amount, $description, $reference) {

            $newBalance = $wallet->withdrawable + $amount;

            $wallet->update([
                'withdrawable' => $newBalance
            ]);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'transaction_type' => 'CREDIT',
                'amount' => $amount,
                'balance' => $newBalance,
                'description' => $description,
                'reference' => $reference ?? Str::random(10),
                'balance_type' => WalletBalanceType::WinningsBalance->value,
                'transaction_action' => WalletTransactionAction::WinningsCredited->value
            ]);
        });
    }

    public function creditFundingAccount(
        Wallet $wallet,
        float $amount,
        string $description,
        string $reference = null
    ): void {
        DB::transaction(function () use ($wallet, $amount, $description, $reference) {

            $newBalance = $wallet->non_withdrawable + $amount;

            $wallet->update([
                'non_withdrawable' => $newBalance
            ]);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'transaction_type' => 'CREDIT',
                'amount' => $amount,
                'balance' => $newBalance,
                'description' => $description,
                'reference' => $reference ?? Str::random(10),
                'balance_type' => WalletBalanceType::CreditsBalance->value,
                'transaction_action' => WalletTransactionAction::WalletFunded->value
            ]);
        });
    }

    public function debit(Wallet $wallet, float $amount, string $description, string $reference = null, string $balanceAccount, string $action): void
    {
        $balanceAmount = 0;
        $balanceType = '';

        if ($balanceAccount == "non_withdrawable") {
            $wallet->non_withdrawable -= $amount;
            $wallet->save();

            $balanceAmount = $wallet->non_withdrawable;
            $balanceType = WalletBalanceType::CreditsBalance->value;
        }
        if ($balanceAccount == "bonus") {
            $wallet->bonus -= $amount;
            $wallet->save();

            $balanceAmount = $wallet->bonus;
            $balanceType = WalletBalanceType::BonusBalance->value;
        }
        if ($balanceAccount == "withdrawable") {
            $wallet->withdrawable -= $amount;
            $wallet->save();

            $balanceAmount = $wallet->withdrawable;
            $balanceType = WalletBalanceType::WinningsBalance->value;
        }

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'transaction_type' => 'DEBIT',
            'amount' => $amount,
            'balance' => $balanceAmount,
            'description' => $description,
            'reference' => $reference ?? Str::random(10),
            'balance_type' => $balanceType ,
            'transaction_action' => $action
        ]);
    }

    public function creditBonusAccount(Wallet $wallet, float $amount, string $description, string $reference = null): void
    {
        DB::transaction(function () use ($wallet, $amount, $description, $reference) {

            $newBalance = $wallet->bonus + $amount;

            $wallet->update([
                'bonus' => $newBalance
            ]);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'transaction_type' => 'CREDIT',
                'amount' => $amount,
                'balance' => $newBalance,
                'description' => $description,
                'reference' => $reference ?? Str::random(10),
                'balance_type' => WalletBalanceType::BonusBalance->value,
                'transaction_action' => WalletTransactionAction::BonusCredited->value
            ]);
        });
    }


    public function hasFundedBefore($user)
    {
        return WalletTransaction::where('wallet_id', $user->wallet->id)
            ->where('transaction_action', WalletTransactionAction::WalletFunded->value)
            ->exists();
    }

}

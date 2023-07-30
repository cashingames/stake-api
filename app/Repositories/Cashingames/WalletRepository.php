<?php

namespace App\Repositories\Cashingames;

use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Enums\WalletTransactionType;
use App\Models\Staking;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Repositories\Cashingames\WalletTransactionDto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletRepository
{

    public function getWalletByUserId(int $userId): mixed
    {
        return Wallet::where('user_id', $userId)->firstOrFail();
    }
    public function getWalletBalance($user, WalletBalanceType $walletType)
    {
        $wallet = $user->wallet;
        switch ($walletType) {
            case WalletBalanceType::CreditsBalance:
                return $wallet->non_withdrawable;
            case WalletBalanceType::BonusBalance:
                return $wallet->bonus;
            case WalletBalanceType::WinningsBalance:
                return $wallet->withdrawable;
            default:
                throw new \App\Exceptions\UnknownFeatureException("Invalid wallet balance type");
        }
    }

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
        $stakes = Staking::selectRaw('sum(amount_staked) as amount_staked, sum(amount_won) as amount_won')
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->first();
        $amountStaked = $stakes?->amount_staked ?? 0;
        $amountWon = $stakes?->amount_won ?? 0;

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

    public function hasFundedBefore($user)
    {
        return WalletTransaction::where('wallet_id', $user->wallet->id)
            ->where('transaction_action', WalletTransactionAction::WalletFunded->value)
            ->exists();
    }

    public function getWalletTransactions($wallet, $walletType)
    {
        return $wallet->transactions()
            ->select(
                'wallet_transactions.id as id',
                'transaction_type as type',
                'amount',
                'description',
                'wallet_transactions.created_at as transactionDate'
            )
            ->where('balance_type', $walletType)
            ->orderBy('wallet_transactions.created_at', 'desc')
            ->paginate(100);
    }

    public function addTransaction(WalletTransactionDto $dto): mixed
    {
        $result = null;
        switch ($dto->transactionType) {
            case WalletTransactionType::Credit:
                $result = $this->credit($dto);
                break;
            case WalletTransactionType::Debit:
                $result = $this->debit($dto);
                break;
            default:
                throw new \App\Exceptions\UnknownFeatureException("Invalid wallet transaction type");
        }

        return $result;
    }

    private function debit(WalletTransactionDto $dto): mixed
    {
        $result = null;
        switch ($dto->balanceType) {
            case WalletBalanceType::CreditsBalance:
                $result = $this->removeDeposit($dto);
                break;
            case WalletBalanceType::BonusBalance:
                $result = $this->removeBonus($dto);
                break;
            case WalletBalanceType::WinningsBalance:
                $result = $this->removeWinnings($dto);
                break;
            default:
                throw new \App\Exceptions\UnknownFeatureException("Invalid wallet balance type");
        }

        return $result;
    }

    private function credit(WalletTransactionDto $dto): mixed
    {
        $result = null;
        switch ($dto->balanceType) {
            case WalletBalanceType::CreditsBalance:
                $result = $this->addDeposit($dto);
                break;
            case WalletBalanceType::BonusBalance:
                $result = $this->addBonus($dto);
                break;
            case WalletBalanceType::WinningsBalance:
                $result = $this->addWinnings($dto);
                break;
            default:
                throw new \App\Exceptions\UnknownFeatureException("Invalid wallet balance type");
        }

        return $result;
    }


    private function addDeposit(
        WalletTransactionDto $dto
    ): mixed {

        $transaction = null;
        DB::transaction(function () use ($dto, &$transaction) {

            $wallet = $this->getWalletByUserId($dto->userId);
            $balance = $wallet->non_withdrawable += $dto->amount;
            $wallet->save();

            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'amount' => $dto->amount,
                'balance' => $balance,
                'description' => $dto->description,
                'transaction_action' => $dto->action,
                'reference' => $dto->reference,
                'balance_type' => WalletBalanceType::CreditsBalance->value,
                'transaction_type' => WalletTransactionType::Credit->value,
            ]);
        });

        return $transaction;
    }

    private function removeDeposit(WalletTransactionDto $dto): mixed
    {
        $transaction = null;
        DB::transaction(function () use ($dto, &$transaction) {

            $wallet = $this->getWalletByUserId($dto->userId);
            $wallet->non_withdrawable -= $dto->amount;
            $wallet->save();

            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'amount' => $dto->amount,
                'balance' => $wallet->non_withdrawable,
                'description' => $dto->description,
                'transaction_action' => $dto->action,
                'reference' => $dto->reference,
                'balance_type' => WalletBalanceType::CreditsBalance->value,
                'transaction_type' => WalletTransactionType::Debit->value,
            ]);

        });

        return $transaction;
    }

    private function addWinnings(WalletTransactionDto $dto): mixed
    {

        $transaction = null;
        DB::transaction(function () use ($dto, &$transaction) {

            $wallet = $this->getWalletByUserId($dto->userId);
            $balance = $wallet->withdrawable += $dto->amount;
            $wallet->save();

            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'amount' => $dto->amount,
                'balance' => $balance,
                'description' => $dto->description,
                'transaction_action' => $dto->action,
                'reference' => $dto->reference,
                'balance_type' => WalletBalanceType::WinningsBalance->value,
                'transaction_type' => WalletTransactionType::Credit->value,
            ]);
        });

        return $transaction;
    }

    private function removeWinnings(WalletTransactionDto $dto): mixed
    {

        $transaction = null;
        DB::transaction(function () use ($dto, &$transaction) {

            $wallet = $this->getWalletByUserId($dto->userId);
            $balance = $wallet->withdrawable -= $dto->amount;
            $wallet->save();

            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'amount' => $dto->amount,
                'balance' => $balance,
                'description' => $dto->description,
                'transaction_action' => $dto->action,
                'reference' => $dto->reference,
                'balance_type' => WalletBalanceType::WinningsBalance->value,
                'transaction_type' => WalletTransactionType::Debit->value,
            ]);

        });

        return $transaction;
    }


    private function addBonus(
        WalletTransactionDto $dto
    ): mixed {

        $transaction = null;
        DB::transaction(function () use ($dto, &$transaction) {

            $wallet = $this->getWalletByUserId($dto->userId);
            $balance = $wallet->bonus += $dto->amount;
            $wallet->save();

            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'amount' => $dto->amount,
                'balance' => $balance,
                'description' => $dto->description,
                'transaction_action' => $dto->action,
                'reference' => $dto->reference,
                'balance_type' => WalletBalanceType::BonusBalance->value,
                'transaction_type' => WalletTransactionType::Credit->value,
            ]);
        });

        return $transaction;
    }


    private function removeBonus(WalletTransactionDto $dto): mixed
    {

        $transaction = null;
        DB::transaction(function () use ($dto, &$transaction) {

            $wallet = $this->getWalletByUserId($dto->userId);
            $balance = $wallet->bonus -= $dto->amount;
            $wallet->save();

            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'amount' => $dto->amount,
                'balance' => $balance,
                'description' => $dto->description,
                'transaction_action' => $dto->action,
                'reference' => $dto->reference,
                'balance_type' => WalletBalanceType::BonusBalance->value,
                'transaction_type' => WalletTransactionType::Debit->value,
            ]);

        });

        return $transaction;
    }


}
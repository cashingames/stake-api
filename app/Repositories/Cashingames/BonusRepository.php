<?php

namespace App\Repositories\Cashingames;

use App\Enums\BonusType;
use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Models\Bonus;
use App\Models\User;
use App\Models\UserBonus;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BonusRepository
{
    public function giveBonus(Bonus $bonus, User $user)
    {
        UserBonus::create([
            'user_id' => $user->id,
            'bonus_id' => $bonus->id
        ]);
    }

    public function activateBonus(Bonus $bonus, User $user, float $amount)
    {
        UserBonus::where('user_id', $user->id)
            ->where('bonus_id', $bonus->id)
            ->where('is_on', false)->update([
                'is_on' => true,
                'amount_credited' => $amount,
                'amount_remaining_after_staking' => $amount
            ]);
    }

    public function deactivateBonus(Bonus $bonus, User $user)
    {
        $userBonus = UserBonus::where('user_id', $user->id)
            ->where('bonus_id', $bonus->id)
            ->where('is_on', true)->first();

        $wallet = Wallet::where('user_id', $user->id)->first();

        $wallet->bonus = $wallet->bonus - $userBonus->amount_remaining_after_staking;
        $wallet->save();

        $userBonus->update([
            'is_on' => false
        ]);
    }

    public function updateWonAmount(Bonus $bonus, User $user, float $amount)
    {

        $userBonus = UserBonus::where('user_id', $user->id)
            ->where('bonus_id', $bonus->id)
            ->where('is_on', true)->first();

        $userBonus->total_amount_won = $userBonus->total_amount_won + $amount;
        $userBonus->amount_remaining_after_staking = $userBonus->amount_remaining_after_staking + $amount;

        $userBonus->save();
    }

    public function getActiveUserRegistrationBonusesToExpire(): Collection
    {
        return UserBonus::where('is_on', true)
            ->where('created_at', '<=', now()->subDays(7))
            ->whereHas('bonus', function ($query) {
                $query->where('name', BonusType::RegistrationBonus->value);
            })
            ->with('user.wallet')
            ->get();
    }

    public function expireBonuses(Collection $activeRegistrationBonuses)
    {
        DB::transaction(function () use ($activeRegistrationBonuses) {

            //remove bonus from users' wallet
            $activeRegistrationBonuses
                ->reject(function (UserBonus $bonus) {
                    return $bonus->amount_remaining_after_staking == 0;
                })
                ->each(function ($userBonus) {
                    $userBonus->user->wallet->update([
                        'bonus' => $userBonus->user->wallet->bonus - $userBonus->amount_remaining_after_staking
                    ]);
                });

            if ($activeRegistrationBonuses->isEmpty()) {
                return;
            }

            $activeRegistrationBonuses->toQuery()->update([
                'is_on' => false
            ]);
        });
    }

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

    public function deductFromUserBonuses($amount)
    {
        $userBonuses = UserBonus::where('user_id', auth()->user()->id)
            ->where('is_on', true)
            ->where('amount_remaining_after_staking', '>', 0)
            ->orderBy('amount_remaining_after_staking')
            ->get();

        $amountRemainingAfterDeduction = $amount;

        foreach ($userBonuses as $userBonus) {

            if ($amountRemainingAfterDeduction == 0) {
                return;
            }
            if (($userBonus->amount_remaining_after_staking - $amountRemainingAfterDeduction) < 0) {
                $userBonus->amount_remaining_after_staking = 0;
                $userBonus->save();

                $amountRemainingAfterDeduction -= $userBonus->amount_remaining_after_staking;
            }
            elseif (($userBonus->amount_remaining_after_staking - $amountRemainingAfterDeduction) >= 0) {
                $userBonus->amount_remaining_after_staking -= $amountRemainingAfterDeduction;
                $userBonus->save();

                $amountRemainingAfterDeduction = $userBonus->amount_remaining_after_staking - $amountRemainingAfterDeduction;
            }
        }
    }
}

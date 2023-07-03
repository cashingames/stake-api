<?php

namespace App\Repositories\Cashingames;

use App\Enums\BonusType;
use App\Models\Bonus;
use App\Models\User;
use App\Models\UserBonus;
use App\Models\Wallet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

    //#[CodeCoverageIgnore]
    public function getActiveUsersBonuses(BonusType $bonusType): Collection
    {
        return UserBonus::where('is_on', true)
            ->whereHas('bonus', function ($query) use ($bonusType) {
                $query->where('name', $bonusType->value);
            })
            ->with('user.wallet')
            ->get();
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
                    return $bonus->amount_remaining_after_staking === 0;
                })
                ->each(function ($userBonus) {
                    $userBonus->user->wallet->update([
                        'bonus' => $userBonus->user->wallet->bonus - $userBonus->amount_remaining_after_staking
                    ]);
                });

            $activeRegistrationBonuses->toQuery()->update([
                'is_on' => false
            ]);
        });
    }

    public function getWeeklyUserLosses(Carbon $startDate, Carbon $endDate)
    {
      
        $usersWithLosses = DB::select("SELECT user_id, sum(amount_won), sum(amount_staked), (sum(amount_won) - sum(amount_staked)) as loss, (sum(amount_won) - sum(amount_staked)) * 0.1 as cashback
        FROM stakings
        
        GROUP BY user_id
        HAVING loss < 0
        ");

        return collect($usersWithLosses);
    }
}

<?php

namespace App\Services;

use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Models\ChallengeStaking;
use App\Models\ExhibitionStaking;
use App\Models\GameSession;
use App\Models\Staking;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Handling staking scenarios.
 */

class StakingService
{
    private $user;
    private $mode;

    public function __construct(User $user, $mode = null)
    {
        $this->user = $user;
        $this->mode = $mode;
    }

    public function stakeAmount($stakingAmount)
    {
        $this->user->wallet->non_withdrawable -= $stakingAmount;
        $this->user->wallet->save();

        WalletTransaction::create([
            'wallet_id' => $this->user->wallet->id,
            'transaction_type' => 'DEBIT',
            'amount' => $stakingAmount,
            'balance' => $this->user->wallet->non_withdrawable,
            'description' => 'Placed a staking of ' . $stakingAmount,
            'reference' => Str::random(10),
            'balance_type' => WalletBalanceType::CreditsBalance->value,
            'description_action' => WalletTransactionAction::StakingPlaced->value
        ]);

        $odd = 1;

        $staking = Staking::create([
            'amount_staked' => $stakingAmount,
            'odd_applied_during_staking' => $odd,
            'user_id' => $this->user->id //@TODO remove from exhibition staking, not in use
        ]);

        return $staking->id;
    }

    public function createChallengeStaking($stakingId, $challengeId)
    {
        return ChallengeStaking::create([
            'challenge_id' => $challengeId,
            'staking_id' => $stakingId,
            'user_id' => Staking::find($stakingId)->user_id
        ]);
    }


}

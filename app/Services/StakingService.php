<?php

namespace App\Services;

use App\Models\Staking;
use App\Models\TriviaStaking;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Handling staking scenarios.
 */

class StakingService
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function stakeAmount($stakingAmount)
    {
        $this->user->wallet->balance -= $stakingAmount;
        $this->user->wallet->save();

        WalletTransaction::create([
            'wallet_id' => $this->user->wallet->id,
            'transaction_type' => 'DEBIT',
            'amount' => $stakingAmount,
            'balance' => $this->user->wallet->balance,
            'description' => 'Placed a staking of ' . $stakingAmount,
            'reference' => Str::random(10),
        ]);

        $staking = Staking::create([
            'user_id' => $this->user->id,
            'amount' => $stakingAmount,
            'standard_odd' => 1
        ]);

        Log::info($stakingAmount . ' staking made for ' . $this->user->username);
        return $staking->id;
    }

    public function createTriviaStaking($stakingId, $triviaId)
    {
        TriviaStaking::create([
            'trivia_id' => $triviaId,
            'staking_id' => $stakingId
        ]);
    }

}

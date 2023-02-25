<?php

namespace App\Services;

use App\Models\ChallengeStaking;
use App\Models\ExhibitionStaking;
use App\Models\Staking;
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
    private $mode;

    public function __construct(User $user, $mode=null)
    {
        $this->user = $user;
        $this->mode = $mode;
    }

    public function stakeAmount($stakingAmount)
    {
        $this->user->wallet->non_withdrawable_balance -= $stakingAmount;
        $this->user->wallet->save();

        WalletTransaction::create([
            'wallet_id' => $this->user->wallet->id,
            'transaction_type' => 'DEBIT',
            'amount' => $stakingAmount,
            'balance' => $this->user->wallet->non_withdrawable_balance,
            'description' => 'Placed a staking of ' . $stakingAmount,
            'reference' => Str::random(10),
        ]);

        $odd = 1;

        if ($this->mode == 'exhibition') {
            $oddMultiplierComputer = new StakingOddsComputer();
            $oddMultiplier = $oddMultiplierComputer->compute($this->user, $this->user->getAverageStakingScore());
            $odd = $oddMultiplier['oddsMultiplier'];
        }

        $staking = Staking::create([
            'amount_staked' => $stakingAmount,
            'odd_applied_during_staking' => $odd,
            'user_id' => $this->user->id //@TODO remove from exhibition staking, not in use
        ]);

        Log::info($stakingAmount . ' staking made for ' . $this->user->username);
        return $staking->id;
    }

    public function createExhibitionStaking($stakingId, $gameSessionId)
    {
        return ExhibitionStaking::create([
            'game_session_id' => $gameSessionId,
            'staking_id' => $stakingId
        ]);
    }

    public function createChallengeStaking($stakingId, $challengeId)
    {
        return ChallengeStaking::create([
            'challenge_id' => $challengeId,
            'staking_id' => $stakingId,
            'user_id' => Staking::find($stakingId)->user_id
        ]);
    }

    public function getRecentStakingSessions()
    {
    }

}

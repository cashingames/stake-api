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
    private $user, $mode;

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

        if ($this->mode == 'exhibition'){
            $oddMultiplierComputer = new StakingOddsComputer();
            $oddMultiplier = $oddMultiplierComputer->compute($this->user, $this->user->getAverageStakingScore());
            $odd = $oddMultiplier['oddsMultiplier'];
        }
       
        $staking = Staking::create([
            'user_id' => $this->user->id,
            'amount_staked' => $stakingAmount,
            'odd_applied_during_staking' => $odd
        ]);

        Log::info($stakingAmount . ' staking made for ' . $this->user->username);
        return $staking->id;
    }

    public function createExhibitionStaking($stakingId, $gameSessionId)
    {
        $record = ExhibitionStaking::create([
            'game_session_id' => $gameSessionId,
            'staking_id' => $stakingId
        ]);

        return $record;
    }

    public function createChallengeStaking($stakingId, $challengeId){
        $record = ChallengeStaking::create([
            'challenge_id' => $challengeId,
            'staking_id' => $stakingId,
            'user_id' => Staking::find($stakingId)->user_id
        ]);

        return $record;
    }

}

<?php

namespace App\Console\Commands;

use App\Actions\SendPushNotification;
use App\Models\Challenge;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RefundExpiredChallengeStakingAmount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'challenge:staking-refund';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refund challenger of staked amount on expired challenge';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Challenge::toBeExpired()
            ->chunk(500, function ($challenges) {
                foreach ($challenges as $challenge) {
                    $challengeStakingRecord = $challenge->stakings()->oldest()->first();

                    if (!is_null($challengeStakingRecord)) {
                        if (!is_null($challengeStakingRecord->user)) {
                            $challengeStakingRecord->user->wallet->update([
                                'non_withdrawable_balance' => ($challengeStakingRecord->user->wallet->non_withdrawable_balance + $challengeStakingRecord->staking->amount_staked)
                            ]);
                            $challengeStakingRecord->user->wallet->save();
                            WalletTransaction::create([
                                'wallet_id' => $challengeStakingRecord->user->wallet->id,
                                'transaction_type' => 'CREDIT',
                                'amount' => $challengeStakingRecord->staking->amount_staked,
                                'balance' => $challengeStakingRecord->user->wallet->non_withdrawable_balance,
                                'description' => 'Reversal of Staked Cash',
                                'reference' => Str::random(10),
                            ]);

                            (new SendPushNotification())->sendChallengeStakingRefundNotification($challengeStakingRecord->user, $challenge);
                        }
                    }
                    $challenge->update(['expired_at' => now()]);
                }
            });
    }
}

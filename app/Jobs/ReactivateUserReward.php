<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReactivateUserReward implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(User $user): void
    {
        $userLastRecord = $user->rewards()
            ->wherePivot('reward_count', -1)
            ->withPivot('reward_count', 'reward_date', 'reward_milestone', 'release_on')
            ->first();

        if ($userLastRecord) {
            $userLastRecord->pivot->reward_count = 0;
            $userLastRecord->pivot->reward_milestone = 1;
            $userLastRecord->save();
        }
    }
}

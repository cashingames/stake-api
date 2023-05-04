<?php

namespace App\Jobs;

use App\Actions\SendPushNotification;
use App\Models\ChallengeRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Notifications\ChallengeStakingRefund;

class SendChallengeRefundNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly ChallengeRequest $request,
        private readonly User $user
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {   
        $this->user->notify(new ChallengeStakingRefund($this->request->amount));
    
    }
}

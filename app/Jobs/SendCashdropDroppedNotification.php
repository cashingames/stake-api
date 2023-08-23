<?php

namespace App\Jobs;

use App\Models\CashdropRound;
use App\Models\User;
use App\Notifications\CashdropDroppedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCashdropDroppedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly string $username,
        private CashdropRound $cashdropRound
    ) {
       
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
       
        User::where('last_activity_time', '>=', $this->cashdropRound->created_at)->chunk(200, function ($users) {
            Log::info('sending cashdrop notifications to users ...', [$users] );
            foreach ($users as $user) {
                $user->notify(new CashdropDroppedNotification(
                    $this->username,
                    $this->cashdropRound->cashdrop->name,
                    $this->cashdropRound->pooled_amount
                ));
            }
        });
    }
}

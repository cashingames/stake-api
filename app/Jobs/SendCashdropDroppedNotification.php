<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\CashdropDroppedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCashdropDroppedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly string $username,
        private readonly float $amount,
        private readonly string $cashdropName
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        User::whereNotNull('phone_verified_at')->where('last_activity_time', '>=', now()->subMonths(2))->chunk(200, function ($users) {
            foreach ($users as $user) {
                $user->notify(new CashdropDroppedNotification(
                    $this->username,
                    $this->amount,
                    $this->cashdropName
                ));
            }
        });
    }
}

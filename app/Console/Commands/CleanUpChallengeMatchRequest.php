<?php

namespace App\Console\Commands;

use App\Models\ChallengeRequest;
use Illuminate\Console\Command;

class CleanUpChallengeMatchRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'challenge-requests:clean-up';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleans up challenge requests table by removing stale unmatched requests';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        ChallengeRequest::toBeCleanedUp()->delete();
    }
}

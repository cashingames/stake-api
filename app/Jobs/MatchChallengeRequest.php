<?php

namespace App\Jobs;

use App\Models\ChallengeRequest;
use App\Actions\TriviaChallenge\MatchRequestAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MatchChallengeRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly ChallengeRequest $requestData,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(MatchRequestAction $action): void
    {
        $action->execute($this->requestData);
    }
}

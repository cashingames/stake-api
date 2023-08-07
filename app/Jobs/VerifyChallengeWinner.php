<?php

namespace App\Jobs;

use App\Actions\TriviaChallenge\VerifyChallengeWinnerAction;
use App\Models\ChallengeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VerifyChallengeWinner implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly ChallengeRequest $request,
        private readonly ChallengeRequest $matchedRequest,

    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(VerifyChallengeWinnerAction $verifyChallengeWinnerAction): void
    {
        $verifyChallengeWinnerAction->execute($this->request, $this->matchedRequest);
    }
}

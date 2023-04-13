<?php

namespace App\Jobs;

use App\Models\ChallengeRequest;
use App\Actions\TriviaChallenge\MatchWithHumanRequestAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MatchWithHumanChallengeRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly ChallengeRequest $requestData,
        private readonly string $env,
    ) {
        Log::info('MatchChallengeRequest job created', [
            'requestData' => $requestData,
            'env' => $env,
        ]);
    }

    /**
     * Execute the job.
     */
    public function handle(MatchWithHumanRequestAction $action): void
    {

        Log::info('MatchWithHumanChallengeRequest Executing', [
            'requestData' => $this->requestData,
            'env' => $this->env,
        ]);

        if ($this->env == 'development') {
            putenv('GOOGLE_CREDENTIALS_ENV=' . ($this->env));
        }

        $action->execute($this->requestData, $this->env);
    }
}

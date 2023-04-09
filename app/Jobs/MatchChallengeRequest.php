<?php

namespace App\Jobs;

use App\Models\ChallengeRequest;
use App\Actions\TriviaChallenge\MatchRequestAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MatchChallengeRequest implements ShouldQueue
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
    public function handle(MatchRequestAction $action): void
    {

        Log::info('MatchChallengeRequest Executing', [
            'requestData' => $this->requestData,
            'env' => $this->env,
        ]);

        if ($this->env == 'development') {
            putenv('GOOGLE_CREDENTIALS_ENV=' . ($this->env));
        }

        $action->execute($this->requestData, $this->env);
    }
}

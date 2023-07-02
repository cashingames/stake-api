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
use App\Traits\Utils\ResolveGoogleCredentials;

class MatchChallengeRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ResolveGoogleCredentials;

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

        $this->setSpecialGoogleCredentialName($this->env);

        $action->execute($this->requestData);
    }
}

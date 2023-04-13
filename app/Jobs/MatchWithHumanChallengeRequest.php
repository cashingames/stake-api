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
use App\Traits\Utils\ResolveGoogleCredentials;

class MatchWithHumanChallengeRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ResolveGoogleCredentials;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly ChallengeRequest $requestData,
        private readonly string $env,
    ) {
        Log::info('MatchWithHumanChallengeRequest job created', [
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

       $this->detectGoogleCredentials($this->env);

        $action->execute($this->requestData, $this->env);
    }

    
}

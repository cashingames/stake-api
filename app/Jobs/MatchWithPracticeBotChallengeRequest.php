<?php

namespace App\Jobs;

use App\Models\ChallengeRequest;
use App\Actions\TriviaChallenge\MatchWithPracticeBotRequestAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Traits\Utils\ResolveGoogleCredentials;

/**
 * 
 * This is currently only used by Practice flow
 */
class MatchWithPracticeBotChallengeRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ResolveGoogleCredentials;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly ChallengeRequest $requestData,
        private readonly string $env,
    ) {
        Log::info('MatchWithBotChallengeRequest job created', [
            'requestData' => $requestData,
            'env' => $env,
        ]);
    }

    /**
     * Execute the job.
     */
    public function handle(MatchWithPracticeBotRequestAction $action): void
    {

        Log::info('MatchWithBotChallengeRequest Executing', [
            'requestData' => $this->requestData,
            'env' => $this->env,
        ]);

        $this->setSpecialGoogleCredentialName($this->env);

        $action->execute($this->requestData);
    }

}
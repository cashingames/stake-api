<?php

namespace App\Actions\TriviaChallenge;

use App\Models\ChallengeRequest;
use Illuminate\Support\Carbon;

class SetDefaultChallengeWinner
{

    public function __construct(
        private readonly ChallengeRequest $request,
        private readonly ChallengeRequest $matchedRequest,
    ) {
    }

    public function execute(ChallengeRequest $challengeRequest)
    {
        if (
            Carbon::parse($this->request->ended_at)->diffInMinutes(now()) >= 1
            && is_null($this->matchedRequest->ended_at)
        ) {
            return $this->request;
        }
        self::execute($challengeRequest);
    }
}

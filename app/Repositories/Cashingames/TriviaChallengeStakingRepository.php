<?php

namespace App\Repositories\Cashingames;

use App\Models\ChallengeRequest;
use Illuminate\Support\Str;
use App\Models\User;

class TriviaChallengeStakingRepository
{


    public function createForMatching(User $user, float $amount, int $categoryId): ChallengeRequest
    {
        $requestId = Str::random(20);

        return ChallengeRequest::create([
            'challenge_request_id' => $requestId,
            'user_id' => $user->id,
            'username' => $user->username,
            'amount' => $amount,
            'category_id' => $categoryId,
            'status' => 'MATCHING',
        ]);
    }

    public function findMatch(ChallengeRequest $challengeRequest): ChallengeRequest
    {
        return ChallengeRequest::where('category_id', $challengeRequest->category_id)
            ->where('status', 'MATCHING')
            ->where('challenge_request_id', '!=', $challengeRequest->challenge_request_id)
            ->where('amount', $challengeRequest->amount)
            ->where('user_id', '!=', $challengeRequest->user_id)
            ->first();
    }

    public function removeFromMatching(ChallengeRequest $challengeRequest): void
    {
        $challengeRequest->delete();
    }

}

<?php

namespace App\Repositories\Cashingames;

use App\Models\ChallengeRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;

class TriviaChallengeStakingRepository
{


    public function createForMatching(User $user, float $amount, int $categoryId): ChallengeRequest
    {
        $requestId = Str::random(20);

        //Updates status to MATCHING by default
        return ChallengeRequest::create([
            'challenge_request_id' => $requestId,
            'user_id' => $user->id,
            'username' => $user->username,
            'amount' => $amount,
            'category_id' => $categoryId,
        ]);
    }

    public function findMatch(ChallengeRequest $challengeRequest): ChallengeRequest|null
    {
        return ChallengeRequest::where('category_id', $challengeRequest->category_id)
            ->where('challenge_request_id', '!=', $challengeRequest->challenge_request_id)
            ->where('amount', $challengeRequest->amount)
            ->where('user_id', '!=', $challengeRequest->user_id)
            ->first();
    }

    public function updateAsMatched(ChallengeRequest $challengeRequest, ChallengeRequest $opponentRequest): void
    {
        $token = Str::uuid()->toString();
        DB::update(
            'UPDATE challenge_requests SET session_token = ?, status = ?
             WHERE challenge_request_id IN (?, ?)',
            [
                $token,
                'MATCHED',
                $challengeRequest->challenge_request_id,
                $opponentRequest->challenge_request_id
            ]
        );
    }

}

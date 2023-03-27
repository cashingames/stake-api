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

}

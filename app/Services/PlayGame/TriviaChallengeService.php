<?php

namespace App\Services\PlayGame;

use App\Actions\Wallet\DebitWalletAction;
use App\Models\ChallengeRequest;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TriviaChallengeService
{

    public function __construct(
        private readonly DebitWalletAction $debitWalletAction
    ) {
    }

    public function create(User $user, array $data): string
    {

        $requestId = Str::random(20);

        DB::transaction(function () use ($user, $data, $requestId) {
            $this->debitWalletAction->execute($user->wallet, $data['amount'], 'Trivia challenge staking request');
            $this->createChallengeRequest($user, $data, $requestId);
        });

        return $requestId;
    }

    private function createChallengeRequest(User $user, array $data, string $requestId): void
    {
        ChallengeRequest::create([
            'challenge_request_id' => $requestId,
            'user_id' => $user->id,
            'username' => $user->username,
            'amount' => $data['amount'],
            'category_id' => $data['category'],
        ]);
    }
}

<?php

namespace App\Services\PlayGame;

use App\Models\ChallengeRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\User;
use App\Actions\Wallet\DebitWalletAction;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;

class TriviaChallengeService
{

    public function __construct(
        private readonly DebitWalletAction $debitWalletAction,
        private readonly TriviaChallengeStakingRepository $triviaChallengeStakingRepository
    ) {
    }

    public function create(User $user, array $data): string
    {
        $response = ChallengeRequest::factory()->make();
        DB::transaction(function () use ($user, $data, &$response) {
            $this->debitWalletAction->execute($user->wallet, $data['amount'], 'Trivia challenge staking request');
            $response = $this
                ->triviaChallengeStakingRepository
                ->createForMatching($user, $data['amount'], $data['category']);
        });

        return $response->challenge_request_id;
    }


}

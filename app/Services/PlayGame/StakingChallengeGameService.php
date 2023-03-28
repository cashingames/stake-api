<?php

namespace App\Services\PlayGame;

use App\Models\ChallengeRequest;
use App\Services\Firebase\FirestoreService;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\User;
use App\Actions\Wallet\DebitWalletAction;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;

class StakingChallengeGameService
{

    public function __construct(
        private readonly DebitWalletAction $debitWalletAction,
        private readonly TriviaChallengeStakingRepository $triviaChallengeStakingRepository,
        private readonly FirestoreService $firestoreService
    ) {
    }
    public function create(User $user, array $data): ChallengeRequest|null
    {
        $response = null;
        DB::transaction(function () use ($user, $data, &$response) {
            $this->debitWalletAction->execute($user->wallet, $data['amount'], 'Trivia challenge staking request');
            $response = $this
                ->triviaChallengeStakingRepository
                ->createForMatching($user, $data['amount'], $data['category']);
        });

        if (!$response) {
            return null;
        }

        $this->firestoreService->createDocument(
            'trivia-challenge-requests',
            $response->challenge_request_id,
            $response->toArray()
        );

        return $response;
    }


}

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
    public function create(User $user, array $data): ChallengeRequest
    {
        $response = ChallengeRequest::factory()->make();
        DB::transaction(function () use ($user, $data, &$response) {
            $this->debitWalletAction->execute($user->wallet, $data['amount'], 'Trivia challenge staking request');
            $response = $this
                ->triviaChallengeStakingRepository
                ->createForMatching($user, $data['amount'], $data['category']);
        });

        $this->firestoreService->setDocument(
            'trivia-challenge-requests',
            $response->challenge_request_id,
            $response->toArray()
        );

        return $response;
    }

    /**
     * Look for a match for the challenge request immediately
     * If found match and start
     * If not found match, matching status
     *
     * @param ChallengeRequest $challengeRequest
     * @return ChallengeRequest|null
     */
    public function match(ChallengeRequest $challengeRequest): ChallengeRequest|null
    {
        $match = $this->triviaChallengeStakingRepository->findMatch($challengeRequest);
        if (!$match) {
            return null;
        }

        //update matched challenge request to matched
        $challengeRequest->status = 'MATCHED';
        $challengeRequest->save();

        $match->status = 'MATCHED';
        $match->save();

        //update matched challenge request to matched in firestore
        $this->firestoreService->setDocument(
            'trivia-challenge-requests',
            $challengeRequest->challenge_request_id,
            ['status' => 'MATCHED']
        );

        $this->firestoreService->setDocument(
            'trivia-challenge-requests',
            $match->challenge_request_id,
            ['status' => 'MATCHED']
        );

        return $match;
    }

}

<?php

namespace App\Services\PlayGame;

use App\Models\ChallengeRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\User;
use App\Services\Firebase\FirestoreService;
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

    public function submit(array $data): ChallengeRequest|null
    {

        $requestId = $data['challenge_request_id'];
        $selectedOptions = $data['selected_options'];

        $challengeRequest = $this->triviaChallengeStakingRepository->getRequestById($requestId);

        if (!$challengeRequest) {
            return null;
        }

        $request = $this
            ->triviaChallengeStakingRepository
            ->updateSubmission($requestId, $selectedOptions);

        $this->firestoreService->updateDocument(
            'trivia-challenge-requests',
            $challengeRequest->challenge_request_id,
            [
                ...$request->toArray(),
                'opponent' => $this
                    ->triviaChallengeStakingRepository
                    ->getMatchedRequest($challengeRequest)
                    ->toArray(),
            ]
        );

        return $request;
    }

}

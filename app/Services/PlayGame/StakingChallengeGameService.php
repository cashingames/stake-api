<?php

namespace App\Services\PlayGame;

use App\Actions\TriviaChallenge\MatchEndWalletAction;
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
        private readonly MatchEndWalletAction $matchEndWalletAction,
        private readonly TriviaChallengeStakingRepository $triviaChallengeStakingRepository,
        private readonly FirestoreService $firestoreService
    ) {
    }
    public function create(User $user, array $data): ChallengeRequest|null
    {
        $response = null;
        DB::transaction(function () use ($user, $data, &$response) {
            $this->debitWalletAction->execute(
                $user->wallet,
                $data['amount'],
                'Trivia challenge staking request'
            );
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
            [
                'challenge_request_id' => $response->challenge_request_id,
                'username' => $response->username,
                'avatar' => $response->user->profile->avatar,
                'status' => $response->status,
            ]
        );

        return $response;
    }

    public function submit(array $data): ChallengeRequest|null
    {
        $requestId = $data['challenge_request_id'];
        $selectedOptions = $data['selected_options'];

        $score = $selectedOptions == null ?
            0 : $this->triviaChallengeStakingRepository->scoreLoggedQuestions($requestId, $selectedOptions);

        [$request, $matchedRequest] = $this
            ->triviaChallengeStakingRepository
            ->updateCompletedRequest($requestId, $score);

        $this->matchEndWalletAction->execute($requestId);
        $request->refresh();
        $matchedRequest->refresh();

        $this->firestoreService->updateDocument(
            'trivia-challenge-requests',
            $request->challenge_request_id,
            [
                'score' => $request->score,
                'status' => $request->status,
                'amount_won' => $request->amount_won,
                'opponent' => [
                    'score' => $matchedRequest->score,
                    'status' => $matchedRequest->status,
                ]
            ]
        );

        $this->firestoreService->updateDocument(
            'trivia-challenge-requests',
            $matchedRequest->challenge_request_id,
            [
                'score' => $matchedRequest->score,
                'status' => $matchedRequest->status,
                'amount_won' => $matchedRequest->amount_won,
                'opponent' => [
                    'score' => $request->score,
                    'status' => $request->status,
                ]
            ]
        );

        return $request;
    }
}

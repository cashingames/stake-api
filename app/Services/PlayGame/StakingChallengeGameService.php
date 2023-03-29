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

    public function submit(User $user, array $data): ChallengeRequest|null
    {

        $requestId = $data['challenge_request_id'];
        $selectedOptions = $data['selected_options'];

        $challengeRequest = $this->triviaChallengeStakingRepository->getRequestById($requestId);

        if (!$challengeRequest) {
            return null;
        }

        $score = $this->calculateScore($user, $challengeRequest);

        return $this
            ->triviaChallengeStakingRepository
            ->updateSubmission($requestId, $score);
    }

    public function calculateScore(User $user, ChallengeRequest $request): int|float
    {
        // $questions = $challengeRequest->questions;
        // foreach of the questions find if the selected option is correct
        // $correctAnswers = 0;
        // foreach ($questions as $question) {
        //     $correctOption = $question->options->where('is_correct', true)->first();
        //     $selectedOption = $selectedOptions->where('question_id', $question->id)->first();
        //     if ($correctOption->id === $selectedOption['option_id']) {
        //         $correctAnswers++;
        //     }
        // }

        return 100;
    }


}

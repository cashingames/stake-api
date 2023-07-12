<?php

namespace App\Services\PlayGame;

use App\Actions\TriviaChallenge\MatchEndWalletAction;
use App\Actions\TriviaChallenge\PracticeMatchEndWalletAction;
use App\Models\ChallengeRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\User;
use App\Services\Firebase\FirestoreService;
use App\Actions\Wallet\DebitWalletAction;
use App\Enums\GameRequestMode;
use App\Enums\WalletTransactionAction;
use App\Models\UserBoost;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Lottery;

class StakingChallengeGameService
{

    public function __construct(
        private readonly DebitWalletAction $debitWalletAction,
        private readonly MatchEndWalletAction $matchEndWalletAction,
        private readonly FirestoreService $firestoreService,
        private readonly TriviaChallengeStakingRepository $triviaChallengeStakingRepository,
        private readonly PracticeMatchEndWalletAction $practiceMatchEndWalletAction
    ) {
    }
    public function create(User $user, array $data): ChallengeRequest|null
    {
        $response = null;
        DB::transaction(function () use ($user, $data, &$response) {
            $this->debitWalletAction->execute(
                $user->wallet,
                $data['amount'],
                'Challenge game stake debited',
                "non_withdrawable",
                WalletTransactionAction::StakingPlaced->value
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

    public function createPracticeRequest(User $user, array $data): ChallengeRequest|null
    {
        $response = $this
            ->triviaChallengeStakingRepository
            ->createPracticeRequestForMatching($user, $data['amount'], $data['category']);

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
        $consumedBoosts = $data['consumed_boosts'] ?? null;

        //fix double submission bug from frontend
        $request = $this->triviaChallengeStakingRepository->getRequestById($requestId);
        if ($request->status == 'COMPLETED') {
            Log::info('CHALLENGE_SUBMIT_ERROR', ['status' => 'SECOND_SUBMISSIONS', 'request' => $request]);
            return $request;
        }

        $score = $selectedOptions == null ?
            0 : $this->triviaChallengeStakingRepository->scoreLoggedQuestions($requestId, $selectedOptions);


        [$request, $matchedRequest] = $this
            ->triviaChallengeStakingRepository
            ->updateCompletedRequest($requestId, $score);

        if ($this->isBot($matchedRequest)) {
            [$matchedRequest, $request] = $this->handleBotSubmission($matchedRequest, $score);
        }

        $this->matchEndWalletAction->execute($requestId);
        $this->updateEndMatchFirestore($request, $matchedRequest);

        if (!is_null($consumedBoosts)) {
            $this->handleConsumedBoosts($consumedBoosts, $request);
        }
        return $request;
    }

    public function submitPracticeChallenge(array $data): ChallengeRequest|null
    {
        $requestId = $data['challenge_request_id'];
        $selectedOptions = $data['selected_options'];

        //fix double submission bug from frontend
        $request = $this->triviaChallengeStakingRepository->getRequestById($requestId);
        if ($request->status == 'COMPLETED') {
            Log::info('PRACTICE_CHALLENGE_SUBMIT_ERROR', ['status' => 'SECOND_SUBMISSIONS', 'request' => $request]);
            return $request;
        }

        $score = $selectedOptions == null ?
            0 : $this->triviaChallengeStakingRepository->scoreLoggedQuestions($requestId, $selectedOptions);

        [$request, $matchedRequest] = $this
            ->triviaChallengeStakingRepository
            ->updateCompletedRequest($requestId, $score);

        [$matchedRequest, $request] = $this->handleBotSubmission($matchedRequest, $score);
        $this->practiceMatchEndWalletAction->execute($requestId, GameRequestMode::CHALLENGE_PRACTICE);
        $this->updateEndMatchFirestore($request, $matchedRequest);

        return $request;
    }

    private function updateEndMatchFirestore(ChallengeRequest $request, ChallengeRequest $matchedRequest)
    {
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
    }

    private function isBot(ChallengeRequest $matchRequest)
    {
        return $matchRequest->user_id == 1;
    }

    private function handleBotSubmission(ChallengeRequest $botRequest, float $opponentScore): array
    {
        $botScore = $this->generateBotScore($opponentScore);
        return $this
            ->triviaChallengeStakingRepository
            ->updateCompletedRequest($botRequest->challenge_request_id, $botScore);
    }

    private function generateBotScore(float $opponentScore): float
    {
        $botScore = 10;

        /**
         * When Odds should win
         * Current odds: 2/5 to help us recoop the lost amount
         */
        Lottery::odds(2, 5)
            ->winner(function () use ($opponentScore, &$botScore) {
                $botScore = ($opponentScore == 10 || $opponentScore == 9) ? 10 : rand($opponentScore, 10);
            })
            ->loser(function () use ($opponentScore, &$botScore) {
                $botScore = rand(0, $opponentScore);
            })
            ->choose();
        return $botScore;
    }

    private function handleConsumedBoosts($consumedBoosts, $request)
    {
        DB::transaction(function () use ($consumedBoosts, $request) {
            foreach ($consumedBoosts as $row) {
                $userBoost = UserBoost::
                    where('user_id', $request->user_id)
                    ->where('boost_id', $row['boost']['id'])->first();

                $userBoost->update([
                    'used_count' => $userBoost->used_count + 1,
                    'boost_count' => $userBoost->boost_count - 1
                ]);

                DB::table('challenge_boosts')->insert([
                    'challenge_request_id' => $request->challenge_request_id,
                    'boost_id' => $row['boost']['id'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        });
    }
}
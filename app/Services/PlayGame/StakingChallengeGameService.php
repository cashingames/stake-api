<?php

namespace App\Services\PlayGame;

use App\Actions\ActionHelpers\ChallengeRequestMatchHelper;
use App\Actions\TriviaChallenge\MatchEndWalletAction;
use App\Actions\TriviaChallenge\PracticeMatchEndWalletAction;
use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionType;
use App\Jobs\VerifyChallengeWinner;
use App\Models\ChallengeRequest;
use App\Repositories\Cashingames\WalletTransactionDto;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\User;
use App\Services\Firebase\FirestoreService;
use App\Enums\WalletTransactionAction;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use App\Repositories\Cashingames\WalletRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Lottery;

class StakingChallengeGameService
{

    public function __construct(
        private readonly WalletRepository $walletRepository,
        private readonly MatchEndWalletAction $matchEndWalletAction,
        private readonly FirestoreService $firestoreService,
        private readonly TriviaChallengeStakingRepository $triviaChallengeStakingRepository,
        private readonly PracticeMatchEndWalletAction $practiceMatchEndWalletAction,
        private readonly ChallengeRequestMatchHelper $challengeHelper
    ) {
    }
    public function create(User $user, array $data): ChallengeRequest|null
    {
        DB::transaction(function () use ($user, $data, &$response) {
            $fundSource = WalletBalanceType::from($data['wallet_type'] ?? WalletBalanceType::CreditsBalance->value);
            $this->walletRepository->addTransaction(
                new WalletTransactionDto(
                    $user->id,
                    $data['amount'],
                    'Challenge game stake debited',
                    $fundSource,
                    WalletTransactionType::Debit,
                    WalletTransactionAction::StakingPlaced
                )
            );

            $response = $this
                ->triviaChallengeStakingRepository
                ->createForMatching($user, $data['amount'], $data['category'], $fundSource->value);
        });

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
            Log::error('CHALLENGE_SUBMIT_ERROR', ['status' => 'SECOND_SUBMISSIONS', 'request' => $request]);
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
        
        if (!is_null($consumedBoosts) || !empty($consumedBoosts)) {
            $this->handleConsumedBoosts($consumedBoosts, $request);
        }

        if (!$this->isBot($matchedRequest) && is_null($matchedRequest->ended_at)) {
            VerifyChallengeWinner::dispatch($request, $matchedRequest)->delay(now()->addMinute());
        } else {
            $this->matchEndWalletAction->execute($requestId);
            $this->challengeHelper->updateEndMatchFirestore($request, $matchedRequest);
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

        [$matchedRequest, $request] = $this->handlePracticeBotSubmission($matchedRequest, $score);
        $this->practiceMatchEndWalletAction->execute($requestId);
        $this->challengeHelper->updateEndMatchFirestore($request, $matchedRequest);

        return $request;
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

    private function handlePracticeBotSubmission(ChallengeRequest $botRequest, float $opponentScore): array
    {
        $botScore = 1;
        Lottery::odds(1, 5)
            ->winner(function () use ($opponentScore, &$botScore) {
                $botScore = rand($opponentScore, 10);
            })
            ->loser(function () use ($opponentScore, &$botScore) {
                $botScore = rand(1, $opponentScore < 10 ? $opponentScore : 10);
            })
            ->choose();

        return $this
            ->triviaChallengeStakingRepository
            ->updateCompletedRequest($botRequest->challenge_request_id, $botScore);
    }

    private function generateBotScore(float $opponentScore): float
    {
        //@NOTE: Make bot win more until we handle jedidiah's winning case
        $botScore = 10;
        Lottery::odds(3, 5)
            ->winner(function () use ($opponentScore, &$botScore) {

                if ($opponentScore > 8) {
                    $botScore = 10;
                } else {
                    $botScore = rand($opponentScore + 1, 10);
                }
            })
            ->loser(function () use ($opponentScore, &$botScore) {
                if ($opponentScore < 3) {
                    $botScore = rand(3, 5); //we don't want both to ever score 0
                } else {
                    $botScore = rand(3, $opponentScore < 10 ? $opponentScore : 10);
                }
            })
            ->choose();
        return $botScore;
    }

    private function handleConsumedBoosts($consumedBoosts, $request)
    {
        DB::transaction(function () use ($consumedBoosts, $request) {
            foreach ($consumedBoosts as $row) {
                DB::update(
                    'UPDATE user_boosts
                    SET used_count = used_count + 1, boost_count = boost_count - 1
                    WHERE user_id = ? AND boost_id = ?',
                    [$request->user_id, $row['boost']['id']]
                );

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

<?php

namespace App\Actions\ActionHelpers;

use App\Enums\GameSessionStatus;
use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Enums\WalletTransactionType;
use App\Repositories\Cashingames\WalletRepository;
use App\Repositories\Cashingames\WalletTransactionDto;
use App\Services\StakeQuestionsHardeningService;
use Illuminate\Support\Collection;
use App\Models\ChallengeRequest;
use App\Services\Firebase\FirestoreService;
use App\Repositories\Cashingames\TriviaQuestionRepository;
use App\Repositories\Cashingames\TriviaChallengeStakingRepository;
use Illuminate\Support\Facades\Cache;

class ChallengeRequestMatchHelper
{

    public function __construct(
        private readonly TriviaChallengeStakingRepository $triviaChallengeStakingRepository,
        private readonly TriviaQuestionRepository $triviaQuestionRepository,
        private readonly StakeQuestionsHardeningService $stakeQuestionsHardeningService,
        private readonly FirestoreService $firestoreService,
        private readonly WalletRepository $walletRepository,
    ) {
    }

    public function processQuestions(ChallengeRequest $challengeRequest, ChallengeRequest $matchedRequest): Collection
    {
        $questions = $this->stakeQuestionsHardeningService
            ->determineQuestions($challengeRequest->user_id, $challengeRequest->category_id);

        $this->triviaChallengeStakingRepository->logQuestions(
            $questions->toArray(),
            $challengeRequest,
            $matchedRequest
        );

        return $questions;
    }

    public function processPracticeQuestions(
        ChallengeRequest $challengeRequest, ChallengeRequest $matchedRequest
    ): Collection
    {

        $questions = Cache::remember(
            'practice_questions_' . $challengeRequest->category_id,
            60 * 60 * 24,
            function () use ($challengeRequest) {
                return $this
                    ->triviaQuestionRepository
                    ->getPracticeQuestionsWithCategoryId($challengeRequest->category_id);
            }
        );

        $this->triviaChallengeStakingRepository->logQuestions(
            $questions->toArray(),
            $challengeRequest,
            $matchedRequest
        );

        return $questions;
    }

    public function updateFirestore(
        ChallengeRequest $challengeRequest,
        ChallengeRequest $matchedRequest,
        Collection $questions
    ): void {

        $this->firestoreService->updateDocument(
            'trivia-challenge-requests',
            $challengeRequest->challenge_request_id,
            [
                'status' => 'MATCHED',
                'questions' => $this->parseQuestions($questions),
                'opponent' => $this->parseOpponent($matchedRequest),
            ]
        );
        $this->firestoreService->updateDocument(
            'trivia-challenge-requests',
            $matchedRequest->challenge_request_id,
            [
                'status' => 'MATCHED',
                'questions' => $this->parseQuestions($questions),
                'opponent' => $this->parseOpponent($challengeRequest),
            ]
        );
    }

    public function updateEndMatchFirestore(ChallengeRequest $request, ChallengeRequest $matchedRequest)
    {
        $request->refresh();
        $matchedRequest->refresh();

        $this->firestoreService->updateDocument(
            'trivia-challenge-requests',
            $request->challenge_request_id,
            [
                'score' => $request->score,
                'status' => $this->cleanFirebaseStatus($request->status),
                'amount_won' => $request->amount_won,
                'opponent' => [
                    'score' => $matchedRequest->score,
                    'status' => $this->cleanFirebaseStatus($matchedRequest->status),
                ]
            ]
        );

        $this->firestoreService->updateDocument(
            'trivia-challenge-requests',
            $matchedRequest->challenge_request_id,
            [
                'score' => $matchedRequest->score,
                'status' => $this->cleanFirebaseStatus($matchedRequest->status),
                'amount_won' => $matchedRequest->amount_won,
                'opponent' => [
                    'score' => $request->score,
                    'status' => $this->cleanFirebaseStatus($request->status),
                ]
            ]
        );
    }

    private function cleanFirebaseStatus(string $status): string
    {
        if ($status == GameSessionStatus::SYSTEM_COMPLETED->value) {
            return GameSessionStatus::COMPLETED->value;
        }

        return $status;
    }

    private function parseQuestions(Collection $questions): array
    {
        return $questions->map(fn ($question) => [
            'id' => $question->id,
            'label' => $question->label,
            'options' => $question->options->map(fn ($option) => [
                'id' => $option->id,
                'title' => $option->title,
                'question_id' => $option->question_id,
            ])->toArray(),
        ])->toArray();
    }

    private function parseOpponent(ChallengeRequest $challengeRequest): array
    {
        return [
            'challenge_request_id' => $challengeRequest->challenge_request_id,
            'username' => $challengeRequest->username,
            'avatar' => $challengeRequest->user->profile->avatar,
            'status' => $challengeRequest->status,
            'is_bot' => $challengeRequest->user->id == 1,
        ];
    }

    public function isCompleted(ChallengeRequest $request): bool
    {
        return $request->status == GameSessionStatus::COMPLETED->value ||
            $request->status == GameSessionStatus::SYSTEM_COMPLETED->value;
    }

    public function isBothCompleted(ChallengeRequest $request, ChallengeRequest $matchedRequest): bool
    {   
        return $this->isCompleted($request) && $this->isCompleted($matchedRequest);
    }

    public function creditWinner(ChallengeRequest $winner): void
    {
        $amountWon = $winner->amount * 2;

        $this->walletRepository->addTransaction(
            new WalletTransactionDto(
                $winner->user_id,
                $amountWon,
                'Challenge game Winnings credited',
                WalletBalanceType::WinningsBalance,
                WalletTransactionType::Credit,
                WalletTransactionAction::WinningsCredited,
            )
        );

        ChallengeRequest::where('challenge_request_id', $winner->challenge_request_id)
            ->update([
                    'amount_won' => $amountWon
                ]);
    }
}

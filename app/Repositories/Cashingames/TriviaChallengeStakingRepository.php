<?php

namespace App\Repositories\Cashingames;

use App\Enums\GameModes;
use App\Models\ChallengeRequest;
use App\Models\Option;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\TriviaChallengeQuestion;

class TriviaChallengeStakingRepository
{

    public function getRequestById(string $requestId): ChallengeRequest|null
    {
        return ChallengeRequest::where('challenge_request_id', $requestId)->first();
    }

    public function createForMatching(User $user, float $amount, int $categoryId): ChallengeRequest
    {
        /**
         * NOTE: Adding more randomness to to test if it will fix the unstable
         * bot score.
         * The current theory is that the request id is not unique enough
         */
        $requestId = uniqid($user->id, true);

        //Updates status to MATCHING by default
        return ChallengeRequest::create([
            'challenge_request_id' => $requestId,
            'user_id' => $user->id,
            'username' => $user->username,
            'amount' => $amount,
            'category_id' => $categoryId,
            'status' => 'MATCHING',
        ])->fresh();
    }

    public function createPracticeRequestForMatching(User $user, float $amount, int $categoryId): ChallengeRequest
    {
        $requestId = uniqid($user->id, true);
        return ChallengeRequest::create([
            'challenge_request_id' => $requestId,
            'user_id' => $user->id,
            'username' => $user->username,
            'amount' => $amount,
            'category_id' => $categoryId,
            'status' => 'MATCHING',
            'challenge_mode' => GameModes::PRACTICE->value
        ])->fresh();
    }


    public function findMatch(ChallengeRequest $challengeRequest): ChallengeRequest|null
    {
        return ChallengeRequest::where('category_id', $challengeRequest->category_id)
            ->where('challenge_request_id', '!=', $challengeRequest->challenge_request_id)
            ->where('amount', $challengeRequest->amount)
            ->where('user_id', '!=', $challengeRequest->user_id)
            ->where('status', 'MATCHING')
            ->first();
    }

    public function getMatchedRequest(ChallengeRequest $challengeRequest): ChallengeRequest|null
    {
        return ChallengeRequest::where('session_token', $challengeRequest->session_token)
            ->where('challenge_request_id', '!=', $challengeRequest->challenge_request_id)
            ->first();
    }

    public function getMatchedRequestById(string $id): ChallengeRequest|null
    {
        return $this->getMatchedRequest($this->getRequestById($id));
    }

    public function updateAsMatched(ChallengeRequest $challengeRequest, ChallengeRequest $opponentRequest): void
    {
        $token = uniqid();
        DB::update(
            'UPDATE challenge_requests SET session_token = ?, status = ?, started_at = ?
             WHERE challenge_request_id IN (?, ?)',
            [
                $token,
                'MATCHED',
                now(),
                $challengeRequest->challenge_request_id,
                $opponentRequest->challenge_request_id,

            ]
        );
    }

    public function logQuestions(
        array $questions,
        ChallengeRequest $challengeRequest,
        ChallengeRequest $opponentRequest
    ): void {

        $result = [];
        foreach ($questions as $question) {
            $item1 = $this->prepareQuestionForLog($question, $challengeRequest);
            $item2 = $this->prepareQuestionForLog($question, $opponentRequest);
            $result[] = $item1;
            $result[] = $item2;
        }

        TriviaChallengeQuestion::insert($result);
    }

    public function scoreLoggedQuestions(string $requestId, array $selectedOptions): int
    {
        $correctOptions = Option::whereIn('id', array_column($selectedOptions, 'option_id'))
            ->where('is_correct', true)
            ->get();

        DB::transaction(function () use ($requestId, $correctOptions) {
            foreach ($correctOptions as $option) {
                DB::update(
                    'UPDATE trivia_challenge_questions SET is_correct = ?
                     WHERE challenge_request_id = ? AND question_id = ?',
                    [
                        true,
                        $requestId,
                        $option['question_id']
                    ]
                );
            }
        });

        return $correctOptions->count();
    }

    public function updateCompletedRequest(string $requestId, int|float $score): array
    {
        $opponent = $this->getMatchedRequestById($requestId);
        ChallengeRequest::where('challenge_request_id', $requestId)
            ->update([
                'status' => 'COMPLETED',
                'score' => $score,
                'ended_at' => now(),
            ]);


        return [$this->getRequestById($requestId), $opponent];
    }


    private function prepareQuestionForLog(array $question, ChallengeRequest $challengeRequest): array
    {
        return [
            'challenge_request_id' => $challengeRequest->challenge_request_id,
            'question_id' => $question['id'],
            'question_label' => $question['label']
        ];
    }



}

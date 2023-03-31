<?php

namespace App\Repositories\Cashingames;

use App\Models\ChallengeRequest;
use App\Models\Option;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\TriviaChallengeQuestion;

class TriviaChallengeStakingRepository
{
    public function createForMatching(User $user, float $amount, int $categoryId): ChallengeRequest
    {
        $requestId = Str::random(20);

        //Updates status to MATCHING by default
        return ChallengeRequest::create([
            'challenge_request_id' => $requestId,
            'user_id' => $user->id,
            'username' => $user->username,
            'amount' => $amount,
            'category_id' => $categoryId,
        ]);
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
        $token = Str::uuid()->toString();
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

    public function getRequestById(string $requestId): ChallengeRequest|null
    {
        return ChallengeRequest::where('challenge_request_id', $requestId)->first();
    }

    public function updateSubmission(string $requestId, mixed $selectedOptions): array
    {
        $correctOptions = Option::whereIn('id', array_column($selectedOptions, 'option_id'))
            ->where('is_correct', true)
            ->get();

        $opponent = $this->getMatchedRequestById($requestId);

        DB::transaction(function () use ($requestId, $correctOptions, $opponent) {
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

            $score = $correctOptions->count();
            if ($opponent->status == 'COMPLETED' && $score < $opponent->score) {
                ChallengeRequest::where('challenge_request_id', $opponent->challenge_request_id)
                    ->update([
                        'amount_won' => $opponent->amount ?? 0
                    ]);
            }

            ChallengeRequest::where('challenge_request_id', $requestId)
                ->update([
                    'status' => 'COMPLETED',
                    'score' => $score,
                    'ended_at' => now(),
                    'amount_won' => $score > $opponent->score ? $opponent->amount : 0
                ]);


        });

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

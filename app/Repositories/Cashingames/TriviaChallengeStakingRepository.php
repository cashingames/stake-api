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
            ->first();
    }

    public function updateAsMatched(ChallengeRequest $challengeRequest, ChallengeRequest $opponentRequest): void
    {
        $token = Str::uuid()->toString();
        DB::update(
            'UPDATE challenge_requests SET session_token = ?, status = ?
             WHERE challenge_request_id IN (?, ?)',
            [
                $token,
                'MATCHED',
                $challengeRequest->challenge_request_id,
                $opponentRequest->challenge_request_id
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

    public function updateSubmission(string $requestId, mixed $selectedOptions): ChallengeRequest|null
    {
        $correctOptions = Option::whereIn('id', array_column($selectedOptions, 'id'))
            ->where('is_correct', true)
            ->get();

        DB::transaction(function () use ($requestId, $selectedOptions, $correctOptions) {
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


        ChallengeRequest::where('challenge_request_id', $requestId)
            ->update([
                'status' => 'COMPLETED',
                'score' => $correctOptions->count(),
            ]);

        return $this->getRequestById($requestId);
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

<?php

namespace App\Services;

use App\Models\Question;
use App\Models\TriviaQuestion;
use Illuminate\Support\Collection;

/**
 * Determining Question Hardening odds of a user
 */

class LiveTriviaQuestionsHardeningService implements QuestionsHardeningServiceInterface
{
    public function determineQuestions(string $userId, string $categoryId, ?string $triviaId): Collection
    {
        $triviaList = TriviaQuestion::where('trivia_id', $triviaId)->inRandomOrder()->pluck('question_id');
        return Question::whereIn('id', $triviaList)->get();
    }


}
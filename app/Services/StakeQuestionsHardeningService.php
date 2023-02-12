<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Support\Collection;

/**
 * Determining Question Hardening odds of a user
 */

class StakeQuestionsHardeningService implements QuestionsHardeningServiceInterface
{
    public function determineQuestions(string $userId, string $categoryId): Collection
    {
        return $this->getEasyQuestions($categoryId);
    }

    private function getEasyQuestions($categoryId): Collection
    {
        return Question::where('category_id', $categoryId)
            ->where('is_published', true)
            ->where('level', 'easy')
            ->inRandomOrder()
            ->take(20)
            ->get();
    }

}
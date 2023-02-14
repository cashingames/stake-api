<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Collection;

/**
 * Determining Question Hardening odds of a user
 */

class StakeQuestionsHardeningService implements QuestionsHardeningServiceInterface
{
    public function determineQuestions(string $userId, int $categoryId, ?string $triviaId): Collection
    {
        return $this->getEasyQuestions($categoryId);
    }

    private function getEasyQuestions(int $categoryId): Collection
    {

        return Category::find($categoryId)->questions()
            ->where('is_published', true)
            ->where('level', 'easy')
            ->inRandomOrder()->take(20)->get();
    }

}
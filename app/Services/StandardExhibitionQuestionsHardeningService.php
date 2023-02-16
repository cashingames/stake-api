<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Collection;

/**
 * Determining Question Hardening odds of a user
 */

class StandardExhibitionQuestionsHardeningService implements QuestionsHardeningServiceInterface
{
    public function determineQuestions(string $userId, string $categoryId, ?string $triviaId): Collection
    {
        return $this->getEasyQuestions($categoryId);
    }

    private function getEasyQuestions(string $categoryId): Collection
    {

        return Category::find($categoryId)->questions()
            ->where('is_published', true)
            ->where('level', 'easy')
            ->inRandomOrder()->take(20)->get();
    }

}
<?php

namespace App\Repositories\Cashingames;

use App\Models\Category;
use Illuminate\Support\Collection;

class TriviaQuestionRepository
{

    public function getRandomEasyQuestionsWithCategoryId(int $categoryId): Collection
    {

        return Category::find($categoryId)
            ->questions()
            ->easy()
            ->inRandomOrder()
            ->take(20)
            ->get();
    }

    public function getPracticeQuestionsWithCategoryId(int $categoryId): Collection
    {
        return Category::find($categoryId)
            ->questions()
            ->easy()
            ->take(30)
            ->get();
    }
}

<?php

namespace App\Repositories\Cashingames;

use App\Models\Category;
use Illuminate\Support\Collection;

class TriviaQuestionRepository
{
    public function getRandomEasyQuestionsWithCategory(Category $category): Collection
    {

        return $category
            ->questions()
            ->easy()
            ->inRandomOrder()
            ->take(10)
            ->get();
    }

    public function getRandomEasyQuestionsWithCategoryId(int $categoryId): Collection
    {

        return Category::find($categoryId)
            ->questions()
            ->easy()
            ->inRandomOrder()
            ->take(20)
            ->get();
    }
}

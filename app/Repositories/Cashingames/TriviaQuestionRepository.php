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

    public function getRandomEasyQuestionsWithCategoryId(Category $category): Collection
    {

        return Category::find($category->id)
            ->questions()
            ->easy()
            ->inRandomOrder()
            ->take(10)
            ->get();
    }
}
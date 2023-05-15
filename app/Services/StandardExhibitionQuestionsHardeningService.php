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
        return $this->getQuestions($categoryId);
    }

    private function getQuestions(string $categoryId): Collection
    {   
       
        $value = Category::find($categoryId)
            ->questions()
            ->where(function ($query) {
                $query->where('level', 'easy')
                    ->orWhere('level', 'medium');
            })
            ->inRandomOrder()
            ->take(20)
            ->get();

        $value = $value->each(function ($i, $k) {

            $i->options->each(function ($ib, $kb) {
                $ib->makeVisible(['is_correct']);
            });
        });



        return $value;
    }
}

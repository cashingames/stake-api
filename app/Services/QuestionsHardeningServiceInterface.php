<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Support\Collection;

/**
 * Interface EloquentRepositoryInterface
 * @package App\Repositories
 */
interface QuestionsHardeningServiceInterface
{
   /**
    * @param array $attributes
    * @return Collection
    */
   public function determineQuestions(int $userId, int $categoryId): Collection;
}
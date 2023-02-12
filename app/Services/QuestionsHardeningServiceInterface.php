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
    * @return Question
    */
   public function determineQuestions(string $userId, string $categoryId, ?string $triviaId): Collection;
}
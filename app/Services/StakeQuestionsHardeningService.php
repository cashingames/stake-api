<?php

namespace App\Services;

use App\Enums\QuestionLevel;
use App\Models\Category;
use App\Models\Staking;
use App\Repositories\Cashingames\TriviaQuestionRepository;
use App\Repositories\Cashingames\WalletRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Determining Question Hardening odds of a user
 * @package App\Services
 *
 */

class StakeQuestionsHardeningService implements QuestionsHardeningServiceInterface
{

    public function __construct(
        private TriviaQuestionRepository $questionRepository
    )
    {
    }

    /*
     * @NOTE: If more complex logic is needed, we need to go back to old code on commit 16ac918
     */
    public function determineQuestions(string $userId, string $categoryId): Collection
    {
        return $this->questionRepository->getRandomEasyQuestionsWithCategoryId($categoryId);
    }
   

}

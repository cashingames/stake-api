<?php

namespace App\Services;

use App\Repositories\Cashingames\TriviaQuestionRepository;
use Illuminate\Support\Collection;

/**
 * Determining Question Hardening odds of a user
 * @package App\Services
 *
 */

class StakeQuestionsHardeningService implements QuestionsHardeningServiceInterface
{

    public function __construct(
        private readonly TriviaQuestionRepository $questionRepository
    )
    {
    }

    /*
     * @NOTE: If more complex logic is needed, we need to go back to old code on commit 16ac918
     *
     * @TODO if user is playing with bonus cash or game, show repeated and easy questions
     */
    public function determineQuestions(int $userId, int $categoryId): Collection
    {

        $arr = [29032509, 29032509, 29031977, 29043260, 29043201, 29031959, 29043239];
        if (in_array($userId, $arr)) {
            return $this->questionRepository->getRandomHardAndMediumQuestionsWithCategoryId($categoryId);
        }
        return $this->questionRepository->getRandomEasyQuestionsWithCategoryId($categoryId);
    }
   

}

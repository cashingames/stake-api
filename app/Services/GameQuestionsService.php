<?php

namespace App\Services;

use App\Models\Question;

/**
* Interface EloquentRepositoryInterface
* @package App\Repositories
*
* Straegy Pattern where this class is the context holding the strategy
*/
class GameQuestionsService implements QuestionsHardeningServiceInterface
{
    protected $strategy;

    /**
     * @var QuestionsHardeningServiceInterface
     */
   public function setStrategy(QuestionsHardeningServiceInterface $strategy)
   {
      $this->strategy = $strategy;
   }

   /**
    * @param array $attributes
    * @return Question
    */
   public function fetchQuestions(): ?Question
   {
         return $this->strategy->determineQuestions();
   }

}

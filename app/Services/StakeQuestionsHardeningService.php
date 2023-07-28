<?php

namespace App\Services;

use App\Repositories\Cashingames\TriviaQuestionRepository;
use App\Repositories\Cashingames\WalletRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Determining Question Hardening odds of a user
 * @package App\Services
 *
 */

class StakeQuestionsHardeningService implements QuestionsHardeningServiceInterface
{

    public function __construct(
        private readonly TriviaQuestionRepository $questionRepository,
        private readonly WalletRepository $walletRepository
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

        $percentWonThisYear = $this->walletRepository
            ->getUserProfitPercentageOnStakingThisYear($userId);

        if ($percentWonThisYear > 30) {

            Log::info(
                'Serving getHardQuestions because percentage won this year is greater than 30%',
                [
                    'user' => auth()->user()->username,
                    'percentWonThisYear' => $percentWonThisYear . '%',
                ]
            );
            return $this->questionRepository
                ->getRandomHardAndMediumQuestionsWithCategoryId($categoryId);
        }
        return $this->questionRepository->getRandomEasyQuestionsWithCategoryId($categoryId);
    }

}

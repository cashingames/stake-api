<?php

namespace App\Services;

use App\Enums\QuestionLevel;
use App\Models\Category;
use App\Models\Staking;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Determining Question Hardening odds of a user
 */

class StakeQuestionsHardeningService implements QuestionsHardeningServiceInterface
{
    public function determineQuestions(string $userId, string $categoryId, ?string $triviaId): Collection
    {
        $user = auth()->user();
        $category = Category::find($categoryId);
        $platformProfitToday = $this->getPlatformProfitToday();
        $percentWonToday = $this->getUserProfitToday($user);
        $questions = null;


        if ($platformProfitToday < 50) {
            Log::info(
                'Serving getHardQuestions due to platform not meeting KPI',
                [
                    'user' => $user->username,
                    'userProfitToday' => $percentWonToday . '%',
                    'platformProfitToday' => $platformProfitToday . '%'
                ]
            );
            return $this->getHardQuestions($user, $category);
        }

        $isNewUser = $this->isNewUser($user);
        if ($isNewUser) {
            Log::info(
                'Serving getRepeatedEasyQuestions for new users',
                [
                    'user' => $user->username,
                    'userProfitToday' => $percentWonToday . '%',
                    'platformProfitToday' => $platformProfitToday . '%'
                ]
            );
            return $this->getRepeatedEasyQuestions($user, $category);
        }

        if ($percentWonToday < -50) { //if user is losing 50% of the time
            $questions = $this->getRepeatedEasyQuestions($user, $category);
            Log::info(
                'Serving getRepeatedEasyQuestions',
                [
                    'user' => $user->username,
                    'userProfitToday' => $percentWonToday . '%',
                    'platformProfitToday' => $platformProfitToday . '%'
                ]
            );
        } elseif ($percentWonToday < 30) {
            $questions = $this->getEasyAndMediumQuestions($category);
            Log::info(
                'Serving getEasyAndMediumQuestions',
                [
                    'user' => $user->username,
                    'percentWonToday' => $percentWonToday,
                    'platformProfitToday' => $platformProfitToday
                ]
            );
        } elseif ($percentWonToday < 200) { //if user is winning 50% of the time
            $questions = $this->getHardQuestions($user, $category);
            Log::info(
                'Serving getHardQuestions',
                [
                    'user' => $user->username,
                    'percentWonToday' => $percentWonToday,
                    'platformProfitToday' => $platformProfitToday
                ]
            );
        } else {
            //notify admin
            Log::info(
                'SERVING_NO_QUESTIONS',
                [
                    'user' => $user->username,
                    'percentWonToday' => $percentWonToday,
                    'platformProfitToday' => $platformProfitToday
                ]
            );
            return collect([]);
        }

        return $questions;
    }

    private function getRepeatedEasyQuestions($user, Category $category): Collection
    {
        $recentQuestions = $this->previouslySeenQuestions($user, $category->id, QuestionLevel::Easy);

        return $category
            ->questions()
            ->easy()
            //eveluate later if 50 is a good number
            ->when($recentQuestions->count() > 50, function ($query) use ($recentQuestions) { //
                return $query->whereIn('questions.id', $recentQuestions);
            })
            ->inRandomOrder()
            ->take(20)
            ->get();
    }

    private function isNewUser($user): bool
    {
        return $user->gameSessions()->count() <= 3;
    }

    /**
     * This includes both new and repeated questions as we are not sure if the
     * user will always win
     *
     * @param mixed $user
     * @param string $categoryId
     * @return Collection
     */
    private function getEasyAndMediumQuestions(Category $category): Collection
    {
        return $category
            ->questions()
            ->easyOrMedium()
            ->inRandomOrder()
            ->take(20)
            ->get();
    }

    /**
     * For medium and hard questions, never repeat.
     *
     * @param mixed $user
     * @param string $categoryId
     * @return Collection
     */
    private function getMediumQuestions($user, Category $category): Collection
    {
        $recentQuestions = $this->previouslySeenQuestions($user, $category->id, QuestionLevel::Medium);

        return $category
            ->questions()
            ->medium()
            ->when($recentQuestions->isNotEmpty(), function ($query) use ($recentQuestions) {
                return $query->whereNotIn('questions.id', $recentQuestions);
            })
            ->inRandomOrder()
            ->take(20)
            ->get();
    }

    /**
     *  Never to be repeated
     *
     * @param mixed $user
     * @param string $categoryId
     * @return Collection
     */
    private function getHardQuestions($user, Category $category): Collection
    {

        $recentQuestions = $this->previouslySeenQuestions($user, $category->id, QuestionLevel::Hard);

        return $category
            ->questions()
            ->hard()
            ->when($recentQuestions->count() > 20, function ($query) use ($recentQuestions) {
                return $query->whereNotIn('questions.id', $recentQuestions);
            })
            ->inRandomOrder()
            ->take(20)
            ->get();
    }

    /**
     *  Never to be repeated
     *
     * @param mixed $user
     * @param string $categoryId
     * @return Collection
     */
    private function getExpertQuestions($user, Category $category): Collection
    {
        $recentQuestions = $this->previouslySeenQuestions($user, $category->id, QuestionLevel::Hard);

        return $category
            ->questions()
            ->expert()
            ->when($recentQuestions->count() > 20, function ($query) use ($recentQuestions) {
                return $query->whereNotIn('questions.id', $recentQuestions);
            })
            ->inRandomOrder()
            ->take(20)
            ->get();
    }

    private function previouslySeenQuestions($user, $categoryId, QuestionLevel $level = null): Collection
    {
        return $user
            ->gameSessionQuestions()
            ->join('questions', 'game_session_questions.question_id', '=', 'questions.id')
            ->where('game_sessions.category_id', $categoryId)
            ->when($level, function ($query, $level) {
                return $query->where('questions.level', $level);
            })
            ->latest('game_sessions.created_at')
            ->take(1000)
            ->pluck('question_id');
    }

    /**
    * To calculate the percentage profit, you need to calculate the difference between the amount received
    * and the initial stake, and then divide by the initial stake and multiply by 100.
    * e.g I staked with 100 and got 15 back how much did I profit in percentage
    * In this case, the amount received was 15 and the initial stake was 100. So the profit would be:
    * (15 – 100) / 100 = -85%
    * Note that the result is negative, which means that there was a loss rather than a profit.
    *
    * If the amount received was greater than the initial stake, the result would be positive.
    * e.g I staked with 100 and got 150 back how much did I profit in percentage
    * In this case, the amount received was 150 and the initial stake was 100. So the profit would be:
    * (150 – 100) / 100 = 50%
    * Note that the result is positive, which means that there was a profit rather than a loss.
    *
    * @param mixed $user
    * @return float
    */

    private function getUserProfitToday($user): float
    {
        $todayStakes = Staking::whereDate('created_at', '=', date('Y-m-d'))
            ->where('user_id', $user->id)
            ->selectRaw('sum(amount_staked) as amount_staked, sum(amount_won) as amount_won')
            ->first();
        $amountStaked = $todayStakes?->amount_staked ?? 0;
        $amountWon = $todayStakes?->amount_won ?? 0;

        if ($amountStaked == 0) {
            return 0;
        }

        if ($amountWon == 0) {
            return -100;
        }

        return ($amountWon - $amountStaked) / $amountStaked;
    }

    /**
     * Platform profit is the opposite of total users profit
     * e,g if users profit is 10%, then platform profit is -10%
     *
     * @return float|int
     */
    private function getPlatformProfitToday()
    {
        $todayStakes = Staking::whereDate('created_at', '=', date('Y-m-d'))
            ->selectRaw('sum(amount_staked) as amount_staked, sum(amount_won) as amount_won')
            ->first();
        $amountStaked = $todayStakes?->amount_staked ?? 0;
        $amountWon = $todayStakes?->amount_won ?? 0;


        /**
         * If no stakes were made today, then the platform is neutral
         * So first user should be lucky
         */
        if ($amountWon == 0) {
            return 100;
        }

        if ($amountStaked == 0) {
            return 0;
        }

        return -(($amountWon - $amountStaked) / $amountStaked);
    }

}

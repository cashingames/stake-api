<?php

namespace App\Services;

use App\Enums\QuestionLevel;
use App\Models\Category;
use App\Models\Staking;
use Illuminate\Support\Collection;
use Log;

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
        $questions = null;


        if ($platformProfitToday < 0.2) { //if platform is not winning up to 20% of amount staked today
            Log::info(
                'Serving getHardQuestions',
                ['user' => $user->username, 'platformProfitToday' => $platformProfitToday]
            );
            return $this->getHardQuestions($user, $category);
        }


        $percentWonToday = $this->getPercentageWonToday($user);
        // if ($percentWonToday <= 0.2) { //if user is losing 80% of the time
        //     $questions = $this->getRepeatedEasyQuestions($user, $categoryId);
        // } else
        if ($percentWonToday < 0.6 || $this->isNewUser($user)) { //if user is losing 50% of the time
            $questions = $this->getRepeatedEasyQuestions($user, $category);
            Log::info(
                'Serving getRepeatedEasyQuestions',
                [
                    'user' => $user->username,
                    'percentWonToday' => $percentWonToday,
                    'platformProfitToday' => $platformProfitToday
                ]
            );
        } elseif ($percentWonToday <= 1) { //if not really winning or losing
            $questions = $this->getEasyAndMediumQuestions($category);
            Log::info(
                'Serving getEasyAndMediumQuestions',
                [
                    'user' => $user->username,
                    'percentWonToday' => $percentWonToday,
                    'platformProfitToday' => $platformProfitToday
                ]
            );
        } elseif ($percentWonToday <= 1.3) { //if user is winning 20% of the time
            $questions = $this->getMediumQuestions($user, $category);
            Log::info(
                'Serving getMediumQuestions',
                [
                    'user' => $user->username,
                    'percentWonToday' => $percentWonToday,
                    'platformProfitToday' => $platformProfitToday
                ]
            );
        } elseif ($percentWonToday <= 1.6) { //if user is winning 50% of the time
            $questions = $this->getHardQuestions($user, $category);
            Log::info(
                'Serving getHardQuestions',
                [
                    'user' => $user->username,
                    'percentWonToday' => $percentWonToday,
                    'platformProfitToday' => $platformProfitToday
                ]
            );
        } elseif ($percentWonToday > 2.1) { //if user is winning 100% of the time
            $questions = $this->getExpertQuestions($user, $category);
            Log::info(
                'Serving getExpertQuestions',
                [
                    'user' => $user->username,
                    'percentWonToday' => $percentWonToday,
                    'platformProfitToday' => $platformProfitToday
                ]
            );
        } else {
            //notify admin
            Log::info('No questions found for user: ' . $user->id);
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
     * Though we said new, it also includes repeated questions, probability of repeated questions is low though
     *
     * @param mixed $user
     * @param string $categoryId
     * @return Collection
     */
    private function getNewAndRepeatedEasyQuestion(Category $category): Collection
    {
        return $category
            ->questions()
            ->easy()
            ->inRandomOrder()
            ->take(20)
            ->get();
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
        // $recentQuestions = $this->previouslySeenQuestions($user, $categoryId, QuestionLevel::Medium);

        return $category
            ->questions()
            ->easyOrMedium()
            // ->when($recentQuestions->count() > 20, function ($query) use ($recentQuestions) {
            //     return $query->whereIn('questions.id', $recentQuestions);
            // })
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


    private function getPercentageWonToday($user): float
    {
        $todayStakes = $user->gameSessions()
            ->join('exhibition_stakings', 'game_sessions.id', '=', 'exhibition_stakings.game_session_id')
            ->join('stakings', 'exhibition_stakings.staking_id', '=', 'stakings.id')
            ->whereDate('game_sessions.created_at', '=', date('Y-m-d'));

        $amountStaked = $todayStakes->sum('stakings.amount_staked') ?? 0;
        $amountWon = $todayStakes->sum('stakings.amount_won') ?? 0;

        if ($amountStaked == 0 || $amountWon == 0) {
            return 1;
        }

        return $amountWon / $amountStaked;
    }

    private function getPlatformProfitToday()
    {
        $todayStakes = Staking::whereDate('created_at', '=', date('Y-m-d'));

        $amountStaked = $todayStakes->sum('stakings.amount_staked') ?? 0;
        $amountWon = $todayStakes->sum('stakings.amount_won') ?? 0;

        if ($amountStaked == 0) {
            return 1;
        }

        return 1 - ($amountWon / $amountStaked);
    }

}
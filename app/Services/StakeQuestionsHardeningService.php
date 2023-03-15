<?php

namespace App\Services;

use App\Enums\QuestionLevel;
use App\Models\Category;
use App\Models\Staking;
use App\Repositories\Cashingames\WalletRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Determining Question Hardening odds of a user
 */

class StakeQuestionsHardeningService implements QuestionsHardeningServiceInterface
{

    public function __construct(
        private WalletRepository $walletRepository
    )
    {
    }

    public function determineQuestions(string $userId, string $categoryId, ?string $triviaId): Collection
    {
        $user = auth()->user();

        $category = Cache::rememberForever('categories', fn() => Category::all())->firstWhere('id', $categoryId);

        $platformProfitToday = Cache::remember('platform-profit-today', 60 * 3, function () {
            return $this
                ->walletRepository
                ->getPlatformProfitPercentageOnStakingToday();
        });


        if ($platformProfitToday < config('trivia.platform_target')) {
            Log::info(
                'Serving getHardQuestions due to platform not meeting KPI',
                [
                    'user' => $user->username,
                    'platformProfitToday' => $platformProfitToday . '%'
                ]
            );
            return $this->getHardQuestions($user, $category);
        }
        $questions = null;
        $isNewUser = $this->isNewUser($user);
        if ($isNewUser) {
            Log::info(
                'Serving getRepeatedEasyQuestions for new users',
                [
                    'user' => $user->username,
                    'userProfitToday' => '0%',
                    'platformProfitToday' => $platformProfitToday . '%'
                ]
            );
            return $this->getRepeatedEasyQuestions($user, $category);
        }

        $percentWonToday = $this->walletRepository
            ->getUserProfitPercentageOnStakingToday($user->id);

        $percentWonThisYear = $this->walletRepository
            ->getUserProfitPercentageOnStakingThisYear($user->id);

        if ($percentWonThisYear > 30) { //if user is losing 50% of the time
            $questions = $this->getHardQuestions($user, $category);
            Log::info(
                'Serving getHardQuestions because percentage won this year is greater than 50%',
                [
                    'user' => $user->username,
                    'userProfitToday' => $percentWonToday . '%',
                    'percentWonThisYear' => $percentWonThisYear . '%',
                    'platformProfitToday' => $platformProfitToday . '%'
                ]
            );
        } elseif ($percentWonToday < 30) {
            $questions = $this->getEasyAndMediumQuestions($category);
            Log::info(
                'Serving getEasyAndMediumQuestions',
                [
                    'user' => $user->username,
                    'percentWonToday' => $percentWonToday . '%',
                    'percentWonThisYear' => $percentWonThisYear . '%',
                    'platformProfitToday' => $platformProfitToday . '%',
                ]
            );
        } elseif ($percentWonToday <= 50) { //if user is winning 50% of the time
            $questions = $this->getHardQuestions($user, $category);
            Log::info(
                'Serving getHardQuestions',
                [
                    'user' => $user->username,
                    'percentWonToday' => $percentWonToday . '%',
                    'percentWonThisYear' => $percentWonThisYear . '%',
                    'platformProfitToday' => $platformProfitToday . '%',
                ]
            );
        } else {
            //notify admin
            Log::info(
                'SERVING_NO_QUESTIONS',
                [
                    'user' => $user->username,
                    'percentWonToday' => $percentWonToday . '%',
                    'percentWonThisYear' => $percentWonThisYear . '%',
                    'platformProfitToday' => $platformProfitToday . '%',
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

    private function isNewUser($user): bool
    {
        return Staking::firstWhere('user_id', $user->id) == null;
    }


}

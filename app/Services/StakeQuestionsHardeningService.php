<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Collection;

/**
 * Determining Question Hardening odds of a user
 */

class StakeQuestionsHardeningService implements QuestionsHardeningServiceInterface
{
    public function determineQuestions(string $userId, string $categoryId, ?string $triviaId): Collection
    {
        $user = auth()->user();

        if ($user->username == 'HeneryJones') {
            return $this->getHardQuestions($user, $categoryId);
        }

        return $this->getEasyQuestions($categoryId);
    }

    private function getEasyQuestions(string $categoryId): Collection
    {
        return Category::find($categoryId)->questions()
            ->where('is_published', true)
            ->where('level', 'easy')
            ->inRandomOrder()->take(20)->get();
    }

    private function getHardQuestions($user, string $categoryId): Collection
    {

        $recentQuestions = $this->getUserAnsweredQuestions($user);

        return Category::find($categoryId)->questions()
            ->where('is_published', true)
            ->where('level', 'hard')
            ->whereNotIn('questions.id', $recentQuestions)
            ->inRandomOrder()->take(20)->get();
    }

    private function getUserAnsweredQuestions($user)
    {
        return $user
            ->gameSessionQuestions()
            ->latest('game_sessions.created_at')->take(1000)->pluck('question_id');
    }

}


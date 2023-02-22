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

        return $this->getHardQuestions($user, $categoryId);

        // return $this->getEasyQuestions($categoryId);
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

        $recentQuestions = $this->previouslySeenQuestionsInCategory($user, $categoryId);

        return Category::find($categoryId)->questions()
            ->where('is_published', true)
            ->where('level', 'hard')
            ->whereNotIn('questions.id', $recentQuestions)
            ->inRandomOrder()->take(20)->get();
    }

    private function previouslySeenQuestionsInCategory($user, $categoryId)
    {
        return $user
            ->gameSessionQuestions()
            ->where('game_sessions.category_id', $categoryId)
            ->latest('game_sessions.created_at')->take(1000)->pluck('question_id');
    }

// private function calculateAmountWonToday($user)
// {
//     $amountWon = $user->exhibitionStakingsToday()->sum('amount_won') -
//         $user->exhibitionStakingsToday()->sum('amount_staked');

//     if ($amountWon < 0) {
//         $amountWon = 0;
//     }

//     return $amountWon;
// }

}
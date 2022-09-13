<?php

namespace App\Services\Odds;

use App\Models\Category;
use App\Models\User;
use Exception;

/**
 * Determining Question Hardening odds of a user
 */

class QuestionsHardeningService
{

    public function determineQuestions(User $user, Category $category,)
    {
        //check user game sessions count
        $gameCount = $user->gameSessions()->count();

        $query = $category
            ->questions()
            ->where('is_published', true);

        if ($gameCount < 3) {
            $questions = $query->whereIn('level', ['medium', 'hard'])->inRandomOrder()->take(20)->get();
            return $questions;
        }

        $averageOfRecentThreeGames = $this->getAverageOfLastThreeGames($user);
        $recentQuestions = $this->getUserAnsweredQuestions($user);

        if ($averageOfRecentThreeGames >= 7) {
            $questions = $query->where('level','hard')->whereNotIn('id', $recentQuestions)->inRandomOrder()->take(20)->get();
            return $questions;
        }

        if ($averageOfRecentThreeGames > 5 && $averageOfRecentThreeGames < 7) {
            $questions = $query->where('level','medium')->whereNotIn('id', $recentQuestions)->inRandomOrder()->take(20)->get();
            return $questions;
        }
        
        if ($averageOfRecentThreeGames <= 5){
            $questions = $query->where('level','easy')->inRandomOrder()->take(20)->get();
            return $questions;
        }
    }

    public function getAverageOfLastThreeGames(User $user)
    {

        // $sumOflastThreeGames = $user->gameSessions()->latest()->take(3)->sum('points_gained');
        $lastThreeGamesAverage = $user->gameSessions()->latest()->limit(3)->get()->avg('correct_count');

        return $lastThreeGamesAverage;

        // return  $sumOflastThreeGames / 3;
    }

    public function getUserAnsweredQuestions(User $user)
    {
        $answeredQuestions = $user->gameSessionQuestions()->latest('game_sessions.created_at')->take(1000)->pluck('question_id');
        return $answeredQuestions;
    }
}

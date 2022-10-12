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
    private $user , $category;

    public function __construct(User $user , Category $category)
    {
        $this->user = $user;
        $this->category = $category;
    }

    public function determineQuestions()
    {
        //check user game sessions count
        $gameCount = $this->user->gameSessions()->count();

        $query = $this->category
            ->questions()
            ->where('is_published', true);

        if ($gameCount < 3) {
            $questions = $query->whereIn('level', ['medium', 'hard'])->inRandomOrder()->take(20)->get();
            return $questions;
        }

        $averageOfRecentThreeGames = $this->getAverageOfLastThreeGames($this->user);
        $recentQuestions = $this->getUserAnsweredQuestions($this->user);

        if ($averageOfRecentThreeGames >= 7) {
            $questions = $query->where('level','hard')->whereNotIn('questions.id', $recentQuestions)->inRandomOrder()->take(20)->get();
            return $questions;
        }

        if ($averageOfRecentThreeGames > 5 && $averageOfRecentThreeGames < 7) {
            $questions = $query->where('level','medium')->whereNotIn('questions.id', $recentQuestions)->inRandomOrder()->take(20)->get();
            return $questions;
        }
        
        if ($averageOfRecentThreeGames <= 5){
            $questions = $query->where('level','easy')->inRandomOrder()->take(20)->get();
            return $questions;
        }
    }

    public function getAverageOfLastThreeGames($mode=null)
    {

        $lastThreeGamesAverage = $this->user->gameSessions()
            ->when($mode === "trivia", function($query){
                $query->whereNotNull("trivia_id");
            })
            ->completed()
            ->latest()
            ->limit(3)
            ->get()
            ->avg('correct_count');

        return $lastThreeGamesAverage;
    }

    public function getUserAnsweredQuestions()
    {
        $answeredQuestions = $this->user->gameSessionQuestions()->latest('game_sessions.created_at')->take(1000)->pluck('question_id');
        return $answeredQuestions;
    }
}

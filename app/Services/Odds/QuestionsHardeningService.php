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
    private $user;
    private $category;

    public function __construct(User $user , Category $category)
    {
        $this->user = $user;
        $this->category = $category;
    }

    public function determineQuestions($isStaking = false)
    {
        if ($isStaking) {
            return $this->getQuestionsForStaking();
        }

        //check user game sessions count
        $gameCount = $this->user->gameSessions()->count();

        $query = $this->category
            ->questions()
            ->where('is_published', true);

        if ($gameCount <= 3) {
            return $query->where('level', 'easy')->inRandomOrder()->take(20)->get();
        }


        $averageOfRecentThreeGames = $this->getAverageOfLastThreeGames($this->user);
        $recentQuestions = $this->getUserAnsweredQuestions($this->user);

        if ($averageOfRecentThreeGames >= 7) {
            return $query->where('level', 'hard')
                                ->whereNotIn('questions.id', $recentQuestions)
                                ->inRandomOrder()->take(20)->get();
        }

        if ($averageOfRecentThreeGames > 5 && $averageOfRecentThreeGames < 7) {
            return $query->where('level', 'medium')
                        ->whereNotIn('questions.id', $recentQuestions)
                        ->inRandomOrder()->take(20)->get();
        }
        
        if ($averageOfRecentThreeGames <= 5){
            return $query->where('level', 'easy')
                        ->inRandomOrder()->take(20)->get();
        }
    }

    public function getAverageOfLastThreeGames($mode=null)
    {
        //@TODO why get all the game sessions and then average them? why not just get the average from the database?

         //if it is live trivia only get average of live trivia, else get average of all game types
        return $this->user->gameSessions()
            ->when($mode === "trivia", function($query){
                $query->whereNotNull("trivia_id");
            })
            ->completed()
            ->latest()
            ->limit(3)
            ->get()
            ->avg('correct_count');
    }

    public function getUserAnsweredQuestions()
    {
        return $this->user
                    ->gameSessionQuestions()
                    ->latest('game_sessions.created_at')->take(1000)->pluck('question_id');
    }

    public function getQuestionsForStaking()
    {
        /**
         * If the user has not staked before or is considered new user, show easy questions
         */
        $initialQuery = $this->category->questions()->where('is_published', true)
                            ->inRandomOrder()->take(20);


        /**
         * If the user is a low scorer show easy questions and repeat questions
         */
        $isNewStaker = $this->user->exhibitionStakings()->latest()->take(1)->count() == 0;
        if ($isNewStaker || $this->lowScorer()) {
            return $initialQuery->where('level', 'easy')->get();
        }

        $recentQuestions = $this->getUserAnsweredQuestions($this->user);
        $initialQuery = $initialQuery->whereNotIn('questions.id', $recentQuestions);
        /**
         * If the user is a medium scorer show combination of medium questions and easy questions
         * - Never repeat questions
         */
        if ($this->mediumScorer()) {
            return $initialQuery->whereIn('level', ['easy','medium'])->get();
        }

        /**
         * If the user is a high scorer show combination of hard questions and medium questions
         */
        if ($this->highScorer()) {
            return $initialQuery->where('level', 'hard')->get();
        }
        
        
    }

    //check if the user has a clean sheet anywhere in the system
    //a clean sheet is when a user has scored 10/10 in a game session
    private function hasCleanSheet()
    {
        $cleanSheet = $this->user->gameSessions()
            ->completed()
            ->where('correct_count', '>', 10)
            ->count();

        return $cleanSheet >= 3;
    }

    private function highestScore()
    {
        return $this->user->gameSessions()
            ->completed()
            ->max('correct_count');
    }

    private function averageScore()
    {
        return $this->user->gameSessions()
            ->completed()
            ->avg('correct_count');
    }

    private function highScorer()
    {
        return $this->highestScore() >= 8;
    }

    private function mediumScorer()
    {
        return $this->highestScore() >= 5 && $this->averageScore() <= 7;
    }

    private function lowScorer()
    {
        return $this->highestScore() < 5;
    }
}

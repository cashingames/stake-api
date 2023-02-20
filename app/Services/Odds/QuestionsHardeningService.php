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
            return $this->getCaseFourForStaking();
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

    public function getCaseFourForStaking()
    {
        return $this->category->questions()
                    ->where('is_published', true)
                    ->where('level', 'easy')
                    ->inRandomOrder()->take(20)->get();
    }

}

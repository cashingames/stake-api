<?php

namespace App\Services;

use App\Models\Category;
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

        $percentWonToday = $this->getPercentageWonToday($user);

        $questions = null;

        // if ($percentWonToday <= 0.2) { //if user is losing 80% of the time
        //     $questions = $this->getEasyRepeatedQuestions($user, $categoryId);
        // } elseif ($percentWonToday < 0.6) { //if user is losing 50% of the time
        //     $questions = $this->getEasyRepeatedQuestions($user, $categoryId);
        // } elseif ($percentWonToday <= 1.1) { //if not really winning or losing (this handles new users)
        //     $questions = $this->getEasyRepeatedQuestions($user, $categoryId);
        // } elseif ($percentWonToday <= 1.3) { //if user is winning 20% of the time
        //     $questions = $this->getEasyAndMediumRepeatedQuestions($user, $categoryId);
        // } elseif ($percentWonToday <= 1.6) { //if user is winning 50% of the time
        //     $questions = $this->getNewMediumQuestions($user, $categoryId);
        // } elseif ($percentWonToday <= 2.1) { //if user is winning 100% of the time (if they have doubled their money)
        //     $questions = $this->getNewHardQuestions($user, $categoryId);
        // } elseif ($percentWonToday > 2.1) { //if user is winning 200% of the time
        //     $questions = $this->getImpossibleQuestions($user, $categoryId);
        // } else {
        //     //notify admin
        //     Log::info('No questions found for user: ' . $user->id);
        // }

        return $questions ?? $this->getEasyRepeatedQuestions($user, $categoryId);
    }

    private function getEasyRepeatedQuestions($user, string $categoryId): Collection
    {
        return Category::find($categoryId)
            ->questions()
            ->easy()
            ->inRandomOrder()->take(20)->get();
    }

    private function getMediumQuestions(string $categoryId): Collection
    {
        return Category::find($categoryId)
            ->questions()
            ->medium()
            ->inRandomOrder()->take(20)->get();
    }

    private function getHardQuestions($user, string $categoryId): Collection
    {

        $recentQuestions = $this->previouslySeenQuestionsInCategory($user, $categoryId);

        return Category::find($categoryId)
            ->questions()
            ->hard()
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


    private function getPercentageWonToday($user)
    {
        $todayStakes = $user->gameSessions()
            ->join('exhibition_stakings', 'game_sessions.id', '=', 'exhibition_stakings.game_session_id')
            ->whereDate('game_sessions.created_at', '=', date('Y-m-d'));

        $amountStaked = $todayStakes->sum('amount_staked') ?? 1;
        $amountWon = $todayStakes->sum('amount_won') ?? 1;

        if ($amountStaked == 0 || $amountWon == 0) {
            return 1;
        }

        return $amountWon / $amountStaked;
    }

}
<?php

namespace App\Services;

use App\Models\User;
use App\Traits\Utils\DateUtils;
use Carbon\Carbon;

class OddsComputer{

    use DateUtils;

    public function compute(User $user, $averageScoreOfRecentGames): array{
        $averageScoreOfRecentGames = is_numeric($averageScoreOfRecentGames) ? floor($averageScoreOfRecentGames) : $averageScoreOfRecentGames;

        $oddsMultiplier = 1;
        $oddsCondition = "no_matching_condition";
        
        if(is_null($averageScoreOfRecentGames) && $this->isFirstEverGame($user)){
            $oddsMultiplier = 10;
            $oddsCondition = "first_ever_game";
        }
        elseif ($this->currentTimeIsInSpecialHours() && $averageScoreOfRecentGames < 4) {
            $oddsMultiplier = 1.5;
            $oddsCondition = "special_hour";
        }
        elseif ($this->isFirstGameAfterFunding($user) && $averageScoreOfRecentGames > 4 && $averageScoreOfRecentGames < 7) {
            $oddsMultiplier = 2;
            $oddsCondition = "first_game_after_funding_wallet";
        }
        elseif ($averageScoreOfRecentGames < 4){
            $oddsMultiplier = 1;
            $oddsCondition = "average_score_less_than_4";
        }
        elseif ($averageScoreOfRecentGames > 5 && $averageScoreOfRecentGames < 7){
            $oddsMultiplier = 1.5;
            $oddsCondition = "average_score_between_5_and_7";
        }
        elseif($averageScoreOfRecentGames > 7){
            $oddsMultiplier = 1;
            $oddsCondition = "average_score_greater_than_7";
        }
        
        return [
            'oddsMultiplier' => $oddsMultiplier,
            'oddsCondition' => $oddsCondition
        ];
    }

    public function currentTimeIsInSpecialHours(){
        $now = date("H");
        $now = $this->toNigeriaTimeZoneFromUtc(date("Y-m-d H:i:s"))->format("H");
        $now .= ":00";
        
        $specialHours = config('odds.special_hours');
        
        return in_array($now, $specialHours);
    }

    public function isFirstGameAfterFunding(User $user){
        $last_funding = $user->transactions()->where('transaction_type', 'credit')->where('description', 'like', "fund%")->latest()->first();
        $last_game = $user->gameSessions()->latest()->first();
        if (is_null($last_funding) || is_null($last_game)){
            return false;
        }

        return Carbon::createFromDate($last_funding->created_at)->gt(Carbon::createFromDate($last_game->created_at));
    }

    public function isFirstEverGame(User $user){
        return $user->gameSessionQuestions()->doesntExist();
    }
}
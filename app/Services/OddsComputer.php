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
        
        if($this->isNewPlayer($user)){
            $oddsMultiplier = 3;
            $oddsCondition = "first_ever_game";
        }
        elseif ($averageScoreOfRecentGames <= 4){
            $oddsMultiplier = 2.5;
            $oddsCondition = "average_score_less_than_5";
        }
        elseif ($averageScoreOfRecentGames >= 5 && $averageScoreOfRecentGames <= 7){
            $oddsMultiplier = 1;
            $oddsCondition = "average_score_between_5_and_7";
        }
        elseif($averageScoreOfRecentGames > 7){
            $oddsMultiplier = 1;
            $oddsCondition = "average_score_greater_than_7";
        }

        if ($this->currentTimeIsInSpecialHours() && $averageScoreOfRecentGames <= 4) {
            $oddsMultiplier += 1.5;
            $oddsCondition .= "_and_special_hour";
            
        }
        if ($this->isFirstGameAfterFunding($user)){
            $oddsMultiplier += 0.5;
            $oddsCondition .= "_and_funded_wallet";
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
        $last_funding = $user->transactions()->where('transaction_type', 'CREDIT')->where('description', 'like', "fund%")->latest()->first();
        $last_game = $user->gameSessions()->latest()->first();
        if (is_null($last_funding) || is_null($last_game)){
            return false;
        }

        return Carbon::createFromDate($last_funding->created_at)->gt(Carbon::createFromDate($last_game->created_at));
    }

    public function isNewPlayer(User $user){
        return $user->gameSessions()->count() <= 5 ;
    }
}
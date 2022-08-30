<?php

namespace App\Services;

use App\Models\User;

class OddsComputer{

    public function compute(User $user, $averageScoreOfRecentGames){
        $averageScoreOfRecentGames = floor($averageScoreOfRecentGames);

        $odds_multiplier = 0;
        if ($averageScoreOfRecentGames < 4){
            $odds_multiplier += 1;
        }
        if ($averageScoreOfRecentGames > 5 && $averageScoreOfRecentGames < 7){
            $odds_multiplier += 1.5;
        }
        if($averageScoreOfRecentGames > 7){
            return $odds_multiplier += 1;
        }

        return $odds_multiplier;
    }
}
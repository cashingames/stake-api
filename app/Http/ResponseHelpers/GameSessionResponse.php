<?php

namespace App\Http\ResponseHelpers;

use App\Enums\FeatureFlags;
use App\Models\GameSession;
use App\Services\FeatureFlag;
use App\Models\ExhibitionStaking;

class GameSessionResponse{

    public function transform(GameSession $gameSession){
        $response = new GameSessionResponse;
        $response->id = $gameSession->id;
        $response->game_mode_id = $gameSession->game_mode_id;
        $response->game_type_id = $gameSession->game_type_id;
        $response->category_id = $gameSession->category_id;
        $response->user_id = $gameSession->user_id;
        $response->start_time = $gameSession->start_time;
        $response->end_time = $gameSession->end_time;
        $response->session_token = $gameSession->session_token;
        $response->correct_count = $gameSession->correct_count;
        $response->wrong_count = $gameSession->wrong_count;
        $response->total_count = $gameSession->total_count;
        $response->points_gained = $gameSession->points_gained;
        $response->state = $gameSession->state;
        $response->trivia_id = $gameSession->trivia_id;
        $response->amount_won = $gameSession->amount_won;

        if (FeatureFlag::isEnabled(FeatureFlags::EXHIBITION_GAME_STAKING) or FeatureFlag::isEnabled(FeatureFlags::TRIVIA_GAME_STAKING)) {
            if ($staking = ExhibitionStaking::where('game_session_id', $gameSession->id)->first()) {
                $response->with_staking = true;
                $response->amount_staked = $staking->staking->amount;
            }    
        }
        
        return $response;
    }
}
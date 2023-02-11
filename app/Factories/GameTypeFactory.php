<?php

namespace App\Factories;

use App\Enums\GameType;
use Illuminate\Support\Arr;

/**
 * Summary of GameTypeFactory
 * mode 1 = exhibition
 * mode 2 = challenge
 */
class GameTypeFactory
{
    public static function detect($payload): GameType
    {
        switch ($payload['mode']) {
            case 2:
                return GameTypeFactory::detectChallengeGames($payload);
            case 1:
            default:
                return GameTypeFactory::detectExhibitionGames($payload);
        }
    }

    public static function detectExhibitionGames($payload): GameType
    {
        $type = GameType::StandardExhibition;
        $hasLiveTrivia = Arr::has($payload, 'trivia') && (bool) $payload['trivia'];
        $hasStaking = Arr::has($payload, 'staking_amount') && (bool) $payload['staking_amount'];

        if ($hasStaking && !$hasLiveTrivia) {
            $type = GameType::StakingExhibition;
        } elseif (!$hasStaking && $hasLiveTrivia) {
            $type = GameType::LiveTrivia;
        } elseif ($hasStaking && $hasLiveTrivia) {
            $type = GameType::LiveTriviaStaking;
        }

        return $type;
    }

    public static function detectChallengeGames($payload): GameType
    {
        if (Arr::has($payload, 'staking_amount')) {
            return GameType::StakingChallenge;
        }

        return GameType::StandardChallenge;
    }


}
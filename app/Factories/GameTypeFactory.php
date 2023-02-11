<?php

namespace App\Factories;

use App\Models\GameType;
use App\Enums\GameType as GameTypeEnum;
use Illuminate\Support\Arr;

/**
 * Summary of GameTypeFactory
 * mode 1 = exhibition
 * mode 2 = challenge
 */
class GameTypeFactory
{
    public static function detect($payload): GameTypeEnum
    {
        $type = "";

        switch ($payload['mode']) {
            case 2:
                $type = GameTypeFactory::detectChallengeGames($payload);
                break;
            case 1:
            default:
                $type = GameTypeFactory::detectExhibitionGames($payload);
                break;
        }

        return $type;
    }

    public static function detectExhibitionGames($payload): GameTypeEnum
    {
        $type = GameTypeEnum::StandardExhibition;
        $hasLiveTrivia = Arr::has($payload, 'trivia') && (bool) $payload['trivia'];
        $hasStaking = Arr::has($payload, 'staking_amount') && (bool) $payload['staking_amount'];

        if ($hasStaking && !$hasLiveTrivia) {
            $type = GameTypeEnum::StakingExhibition;
        } elseif (!$hasStaking && $hasLiveTrivia) {
            $type = GameTypeEnum::LiveTrivia;
        } elseif ($hasStaking && $hasLiveTrivia) {
            $type = GameTypeEnum::LiveTriviaStaking;
        }

        return $type;
    }

    public static function detectChallengeGames($payload): GameTypeEnum
    {
        if (Arr::has($payload, 'staking_amount')) {
            return GameTypeEnum::StakingChallenge;
        }

        return GameTypeEnum::StandardChallenge;
    }


}
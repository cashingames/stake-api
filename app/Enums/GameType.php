<?php

namespace App\Enums;

use Illuminate\Support\Arr;

enum GameType
{
    case LiveTrivia;
    case StandardExhibition;
    case StakingExhibition;
    case StandardChallenge;
    case StakingChallenge;
    case LiveTriviaStaking;

    public static function detect($payload): self
    {
        switch ($payload['mode']) {
            case 2:
                return self::detectChallengeGames($payload);
            case 1:
            default:
                return self::detectExhibitionGames($payload);
        }
    }

    private static function detectExhibitionGames($payload): self
    {
        $type = self::StandardExhibition;
        $hasLiveTrivia = Arr::has($payload, 'trivia') && (bool) $payload['trivia'];
        $hasStaking = Arr::has($payload, 'staking_amount') && (bool) $payload['staking_amount'];

        if ($hasStaking && !$hasLiveTrivia) {
            $type = self::StakingExhibition;
        } elseif (!$hasStaking && $hasLiveTrivia) {
            $type = self::LiveTrivia;
        } elseif ($hasStaking && $hasLiveTrivia) {
            $type = self::LiveTriviaStaking;
        }

        return $type;
    }

    private static function detectChallengeGames($payload): self
    {
        if (Arr::has($payload, 'staking_amount')) {
            return self::StakingChallenge;
        }

        return self::StandardChallenge;
    }

}
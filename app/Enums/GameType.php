<?php

namespace App\Enums;

enum GameType
{
    case LiveTrivia;
    case StandardExhibition;
    case StakingExhibition;
    case StandardChallenge;
    case StakingChallenge;
    case LiveTriviaStaking;
}
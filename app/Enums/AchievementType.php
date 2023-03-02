<?php

namespace App\Enums;

enum AchievementType : string {
    case GAME_PLAYED = "GAME_PLAYED";
    case GAME_BOUGHT = "GAME_BOUGHT";
    case BOOST_BOUGHT = "BOOST_BOUGHT";
    case REFERRAL = "REFERRAL";
    case CHALLENGE_STARTED = "CHALLENGE_STARTED";
    case CHALLENGE_ACCEPTED = "CHALLENGE_ACCEPTED";
    case BOOST_TIME_FREEZE = "Time Freeze";
    case BOOST_SKIP = "Skip";
    case GAME_LEAST = "Least Plan";
    case GAME_DOUBLE = "Double O";
    case GAME_ULTIMATE = "The Ultimate";
    case GAME_PLAYED_FOOTBALL = "football";
    case GAME_PLAYED_MUSIC = "music";
    case GAME_PLAYED_GENERAL = "general";
    case REWARD_CASH = "CASH";
    case REWARD_POINT = "POINTS";
}

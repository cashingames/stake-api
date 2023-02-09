<?php

namespace App\Enums\Contest;

enum ContestType: string
{
    case Livetrivia = "LIVE_TRIVIA";
    case Challenge = "CHALLENGE";
    case Leaderboard = "LEADERBOARD";
}

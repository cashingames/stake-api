<?php

namespace App\Enums;

enum GameModeTypes: string
{
    case PRACTICE = 'PRACTICE';
    case CHALLENGE = 'CHALLENGE';
    case SINGLE_PLAYER = 'SINGLE_PLAYER ';
}
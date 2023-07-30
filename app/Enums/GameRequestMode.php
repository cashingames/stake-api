<?php

namespace App\Enums;

enum GameRequestMode: string
{
    case CHALLENGE = 'CHALLENGE';
    case CHALLENGE_PRACTICE = 'CHALLENGE_PRACTICE';
    case SINGLE_PRACTICE = 'SINGLE_PRACTICE';
}
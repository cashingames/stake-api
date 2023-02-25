<?php

namespace App\Enums;

enum QuestionLevel: string
{
    case Easy = "easy";
    case Medium = "medium";
    case Hard = "hard";
    case Expert = "expert";
}
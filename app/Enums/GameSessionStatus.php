<?php

namespace App\Enums;

enum GameSessionStatus: string
{
    case ONGOING = 'ONGOING';
    case CLOSED = 'CLOSED';
    case PENDING = 'PENDING';
    case DECLINED = 'DECLINED';
    case COMPLETED = 'COMPLETED';
    case MATCHING = 'MATCHING';
    case MATCHED = 'MATCHED';
    case SYSTEM_COMPLETED = 'SYSTEM_COMPLETED';
}
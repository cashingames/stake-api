<?php

namespace App\Enums;

enum GameSessionStatus: string
{
    case ONGOING = 'ONGOING';
    case CLOSED = 'CLOSED';
    case PENDING = 'PENDING';
    case DECLINED = 'DECLINED';
    case EXPIRED = 'EXPIRED';
    case COMPLETED = 'COMPLETED';
}
<?php

namespace App\Enums;

enum UserType : string {
    case GUEST_PLAYER = "GUEST_PLAYER";
    case PERMANENT_PLAYER = "PERMANENT_PLAYER";
}

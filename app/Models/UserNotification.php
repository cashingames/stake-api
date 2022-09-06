<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserNotification extends DatabaseNotification
{
    use HasFactory;

    protected $table = "user_notifications";

    protected $guarded = [];
}

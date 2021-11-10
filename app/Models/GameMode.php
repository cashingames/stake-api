<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameMode extends Model
{
    use HasFactory;

    public function gameSessions(){
        return $this->hasMany(GameSession::class);
    }
}

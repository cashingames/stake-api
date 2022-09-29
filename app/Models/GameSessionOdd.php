<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameSessionOdd extends Model
{
    use HasFactory;

    protected $table = "game_session_odds";

    protected $guarded = [];

    public function gameSession(){
        return $this->belongsTo(GameSession::class, 'game_session_id', 'id');
    }

    public function oddsRule(){
        return $this->belongsTo(OddsRule::class, 'odds_rule_id', 'id');
    }
}

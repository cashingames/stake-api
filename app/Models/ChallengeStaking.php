<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeStaking extends Model
{
    use HasFactory;

    protected $table = "challenge_stakings";

    protected $guarded = [];

    public function staking(){
        return $this->belongsTo(Staking::class);
    }

    public function challengeGameSession(){
        return $this->belongsTo(ChallengeGameSession::class, 'challenge_game_session_id');
    }
}

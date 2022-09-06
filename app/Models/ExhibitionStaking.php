<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExhibitionStaking extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_session_id',
        'staking_id',
    ];

    public function staking()
    {
        return $this->belongsTo(Staking::class);
    }

    public function gameSession()
    {
        return $this->belongsTo(GameSession::class);
    }
}

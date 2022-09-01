<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TriviaStaking extends Model
{
    use HasFactory;

    public function staking()
    {
        return $this->belongsTo(Staking::class);
    }

    public function trivia()
    {
        return $this->belongsTo(Trivia::class);
    }
}

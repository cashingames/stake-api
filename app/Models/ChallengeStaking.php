<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeStaking extends Model
{
    use HasFactory;

    protected $table = "challenge_stakings";

    protected $guarded = [];

    protected $with = ['staking'];

    public function staking(){
        return $this->belongsTo(Staking::class, 'staking_id');
    }

    public function challenge(){
        return $this->belongsTo(Challenge::class, 'challenge_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}

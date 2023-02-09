<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staking extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'amount_staked',
    'odd_applied_during_staking',
    'amount_won'
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function exhibitionStakings()
  {
    return $this->hasMany(ExhibitionStaking::class);
  }

  public function gameSessions()
  {
    return $this->hasManyThrough(GameSession::class, ExhibitionStaking::class);
  }

  public function challengeStakings()
  {
    return $this->hasMany(ChallengeStaking::class);
  }
}

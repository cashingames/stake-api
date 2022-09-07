<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staking extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'amount',
    'standard_odd'
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function exhibitionStakings()
  {
    return $this->hasMany(ExhibitionStaking::class);
  }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plan extends Model
{
  use HasFactory;

  protected $casts = [
    'is_free' => 'boolean',
  ];

  protected $appends = [
    'can_play', 'is_on_campaign'
];

  public function users(){
      return $this->hasMany(User::class);
  }

  public function games(){
      return $this->hasMany(Game::class);
  }

  public function getIsOnCampaignAttribute()
  {
      return config('trivia.campaign.is_on_campaign');
  }

  public function getCanPlayAttribute()
  {
      return config('trivia.campaign.can_play');
  }
}

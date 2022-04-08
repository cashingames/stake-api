<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
  use HasFactory;

  protected $fillable = ['name', 'description'];

  public function questions()
  {
    return $this->hasMany(Question::class);
  }

  public function gameSessions()
  {
    return $this->hasMany(GameSession::class);
  }

  public function gameTypes()
  {
    return $this->hasMany(GameType::class);
  }

  public function users()
  {
    return $this->belongsToMany(Category::class, 'game_sessions')->withPivot('points_gained', 'user_id');
  }

  public function trivias()
  {
    return $this->hasMany(Trivia::class);
  }
}

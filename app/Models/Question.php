<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
  use HasFactory, SoftDeletes;

  protected $with = [
    'options'
  ];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [
    'game_id', 'created_at', 'updated_at'
  ];

  protected $fillable = ['created_by', 'is_published'];
  //
  public function options()
  {
    return $this->hasMany(Option::class)->inRandomOrder();
  }

  public function category()
  {
    return $this->belongsTo(Category::class);
  }

  public function games()
  {
    return $this->hasMany(Game::class);
  }

  public function triviaQuestions()
  {
    return $this->hasMany(TriviaQuestion::class);
  }

  public function getLabelAttribute($value)
  {
    return base64_encode($value);
  }
  public function getLevelAttribute($value)
  {
    return base64_encode($value);
  }
}

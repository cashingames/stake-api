<?php

namespace App\Models;

use App\Enums\QuestionLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

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
    'game_id',
    'created_at',
    'updated_at'
  ];

  protected $fillable = ['created_by', 'is_published'];
  //
  /**
   * The "booted" method of the model.
   */
  protected static function booted(): void
  {
    static::addGlobalScope('published', function (Builder $builder) {
      $builder->where('is_published', true);
    });
  }

  public function options()
  {
    return $this->hasMany(Option::class)->inRandomOrder();
  }

  public function categories()
  {
    return $this->belongsToMany(Category::class, 'categories_questions')->withTimestamps();
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

  public function scopeEasy()
  {
    return $this->whereLevel(QuestionLevel::Easy);
  }

  public function scopeEasyOrMedium()
  {
    return $this->whereLevel(QuestionLevel::Easy)->orWhere('level', QuestionLevel::Medium);
  }

  public function scopeMedium()
  {
    return $this->whereLevel(QuestionLevel::Medium);
  }

  public function scopeHard()
  {
    return $this->whereLevel(QuestionLevel::Hard);
  }

  public function scopeExpert()
  {
    return $this->whereLevel(QuestionLevel::Hard);
  }

  public function getLevelAttribute($value)
  {
    return base64_encode($value);
  }
}
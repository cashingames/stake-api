<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
  use HasFactory;

  protected $fillable = ['name', 'description', 'created_at', 'updated_at','is_enabled'];

  public function questions(): BelongsToMany
  {
    return $this->belongsToMany(Question::class, 'categories_questions', 'category_id', 'question_id');
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

  public function scopeParentCategories($query)
  {
    return $query->where('category_id', 0);
  }

  public function scopeSubcategories($query)
  {
    return $query->where('category_id', '!=', 0);
  }
}

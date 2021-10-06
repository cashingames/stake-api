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

  public function gameTypes()
  {
    return $this->hasMany(GameType::class);
  }

  public function users()
  {
    return $this->belongsToMany(Category::class, 'category_rankings')->withPivot('points_gained', 'user_id');
  }
}

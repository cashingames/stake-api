<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
  use HasFactory;

  protected $with = [
      'options'
  ];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [
    'game_id', 'user_quiz_id','created_at', 'updated_at'
  ];

  protected $fillable =['created_by'];
  //
  public function options(){
      return $this->hasMany(Option::class)->inRandomOrder();
  }

  public function category(){
      return $this->belongsTo(Category::class);
  }

  public function games(){
      return $this->hasMany(Game::class);
  }

  public function getLabelAttribute($value){
    return base64_encode($value);
  }
  public function getLevelAttribute($value){
    return base64_encode($value);
  }
}

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
     'label','level', 'game_id', 'user_quiz_id','created_at', 'updated_at'
  ];

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


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
  use HasFactory;

  public function questions(){
    return $this->hasMany(Question::class);
  }

  public function games(){
    return $this->hasMany(Game::class);
  }
}

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

  public function users(){
      return $this->hasMany(User::class);
  }

  public function games(){
      return $this->hasMany(Game::class);
  }


}

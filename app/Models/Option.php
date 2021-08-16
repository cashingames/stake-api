<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
  use HasFactory;

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [
  'question_id ','created_at', 'updated_at'
  ];

  protected $casts = [
    'is_correct' => 'boolean', 
  ];

  public function question(){
      return $this->belongsTo(Question::class);
  }
}

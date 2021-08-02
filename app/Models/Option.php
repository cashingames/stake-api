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
      'is_correct','question_id ','title','created_at', 'updated_at'
  ];

  public function question(){
      return $this->belongsTo(Question::class);
  }
}

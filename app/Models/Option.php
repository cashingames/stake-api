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
    'question_id ', 'created_at', 'updated_at', 'is_correct'
  ];

  protected $casts = [
    // 'is_correct' => 'boolean',
    // 'title' => 'string'
  ];

  public function question()
  {
    return $this->belongsTo(Question::class);
  }

  public function getIsCorrectAttribute($value)
  {
    return base64_encode($value);
  }
  public function getTitleAttribute($value)
  {
    return base64_encode(str_replace('"', '', $value));
  }

  public function makeVisible($attributes)
  {
    $this->hidden = array_diff($this->hidden, (array) $attributes);

    return $this;
  }

  public function makeHidden($attributes)
  {
    $this->hidden = array_merge($this->hidden, (array) $attributes);

    return $this;
  }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Question;

class Option extends Model
{
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'is_correct', 'created_at', 'updated_at'
    ];

    public function question(){
        return $this->belongsTo(Question::class);
    }
}

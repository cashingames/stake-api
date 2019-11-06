<?php

namespace App\Triva\Models;

use Illuminate\Database\Eloquent\Model;
use App\Triva\Models\Question;

class Option extends Model
{
    //
    public function question(){
        return $this->belongsTo(Question::class);
    }
}

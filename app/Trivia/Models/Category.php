<?php

namespace App\Triva\Model;

use App\Triva\Models\Question;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //
    public function questions(){
        return $this->hasMany(Question::class);
    }
}

<?php

namespace App;

use App\Category;
use Illuminate\Database\Eloquent\Model;
use App\Option;

class Question extends Model
{
    //
    public function options(){
        return $this->hasMany(Option::class);
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }
}

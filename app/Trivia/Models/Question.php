<?php

namespace App\Triva\Models;

use App\Triva\Models\Category;
use Illuminate\Database\Eloquent\Model;
use App\Triva\Models\Option;

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

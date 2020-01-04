<?php

namespace App;

use App\Category;
use Illuminate\Database\Eloquent\Model;
use App\Option;

class Question extends Model
{
    protected $with = [
        'options'
    ];
    //
    public function options(){
        return $this->hasMany(Option::class)->inRandomOrder();
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function games(){
        return $this->hasMany(Game::class);
    }


}

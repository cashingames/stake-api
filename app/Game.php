<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    //
    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function questions(){
        return $this->belongsToMany(Question::class, 'game_questions')->withPivot('is_correct');
    }
}

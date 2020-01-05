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

    public function plan(){
        return $this->belongsTo(Plan::class);
    }

    public function setWinnings(){
        $this->points_gained = $this->plan->point_per_question * $this->correct_count;
        $this->is_winning = $this->points_gained >= $this->plan->minimum_win_points;
        $this->amount_gained =  $this->is_winning ? $this->plan->price_per_point * $this->points_gained : 0;
        return $this;
    }
}

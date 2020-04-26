<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    // protected $appends = ['duration'];

    public function user(){
        return $this->belongsTo(User::class);
    }
    //
    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function questions(){
        return $this->belongsToMany(Question::class, 'game_questions')->withPivot('is_correct', 'created_at');
    }

    public function plan(){
        return $this->belongsTo(Plan::class);
    }

    public function setWinnings(){
        $arr = [8 => 5, 9 => 2, 10 => 1];

        $this->points_gained = $this->plan->point_per_question * $this->correct_count;
        $this->is_winning = $this->points_gained >= $this->plan->minimum_win_points;
        $this->amount_gained =  $this->is_winning ? ($this->plan->price / $arr[$this->correct_count]) : 0;
        return $this;
    }

    public function getRankAttribute($user_id){
        $results = Game::select(
            'select SUM(points_gained) as score, user_id from games
            group by user_id
            order by score desc
            limit 100'
        );

        $user_index = 0;
        // if (count($results) > 0) {
        //     $user_index = collect($results)->search(function ($user) {
        //         return $user->user_id == $user_id;
        //     });
        // }

        if ($user_index === false)
            return 786;

        return $user_index + 1;
    }

}

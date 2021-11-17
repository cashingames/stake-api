<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameSession extends Model
{
    use HasFactory;
    
    protected $fillable = ['plan_id'];


    public function mode()
    {
        return $this->belongsTo(GameMode::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function setWinnings()
    // {
    //     $arr = [8 => 5, 9 => 2, 10 => 1];

    //     $this->points_gained = $this->plan->point_per_question * $this->correct_count;
    //     $this->is_winning = $this->points_gained >= $this->plan->minimum_win_points;
    //     $this->amount_gained =  $this->is_winning ? ($this->plan->price / $arr[$this->correct_count]) : 0;
    //     return $this;
    // }
}

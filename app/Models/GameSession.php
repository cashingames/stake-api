<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameSession extends Model
{
    use HasFactory;
    
    protected $fillable = ['plan_id', 'trivia_id'];


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

    public function trivia(){
        return $this->belongsTo(Trivia::class);
    }
}

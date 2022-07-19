<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeGameSession extends Model
{
    use HasFactory;


    protected $fillable = [
        'challenge_id', 'category_id',
        'game_type_id', 'user_id', 'start_time', 'end_time', 'session_token', 'state', 'correct_count',
        'wrong_count', 'total_count', 'points_gained', 'created_at', 'updated_at'
    ];


    public function mode()
    {
        return $this->belongsTo(GameMode::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function opponent()
    {
        return $this->belongsTo(User::class, 'opponent_id', 'id');
    }

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

  
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\GameSessionStatus;

class GameSession extends Model
{
    use HasFactory;

    protected $fillable = ['plan_id', 'trivia_id','game_mode_id','category_id','game_mode_id',
    'game_type_id','user_id','start_time','end_time','session_token','state','correct_count',
    'wrong_count','total_count','points_gained','amount_won', 'coins_earned', 'created_at','updated_at'];


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


    public function scopeCompleted($query)
    {
        return $query->whereState(GameSessionStatus::COMPLETED);
    }

    public function odds()
    {
        return $this->hasMany(GameSessionOdd::class, 'game_session_id', 'id');
    }

    public function exhibitionStaking()
    {
        return $this->hasOne(ExhibitionStaking::class);
    }

    public function scopePerfectGames($query)
    {
        return $query->where('correct_count', config('trivia.user_scores.perfect_score'));
    }
}

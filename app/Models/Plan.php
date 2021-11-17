<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = ['name' ,'description','price','game_count', 'background_color'];

    protected $casts = [
        'price' => 'integer',
    ];

    protected $appends = [
        'remaining_games'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gameSessions(){
        return $this->hasMany(GameSession::class);
    }
   
    public function getRemainingGamesAttribute()
    {   
        $user = auth()->user();
        $recentGamesCount = GameSession::where('user_id', $user->id)->where('plan_id',$this->id)->
        where('created_at', '>=', Carbon::now()->subDay())->count();

        if($recentGamesCount === null){
            return $this->game_count;
        }
        return $this->game_count - $recentGamesCount;
    }

}

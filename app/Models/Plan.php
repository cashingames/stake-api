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

    public function userPlans()
    {
        return $this->hasMany(UserPlan::class);
    }

    public function gameSessions(){
        return $this->hasMany(GameSession::class);
    }
   
    public function getRemainingGamesAttribute()
    {   
        $user = auth()->user();
        $active_game_count = $this->userPlans->where('user_id', $user->id)->where('plan_id',$this->id)->where('is_active', true)->first();
        
        if($active_game_count !== null){
            return ($this->game_count - $active_game_count->used_count);
        }
       return $this->game_count;
       
    }

}

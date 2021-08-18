<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'milestone', 'medal'];

    protected $appends = [
       'is_claimed'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getIsClaimedAttribute()
    {   
        $user = auth()->user();

        $userAchievement = DB::table('user_achievements')
        ->where('achievement_id',$this->id)
        ->where('user_id', $user->id)->first();
        
        if($userAchievement === null){
           return false;
        }else{
            return true;
        }
    }
}

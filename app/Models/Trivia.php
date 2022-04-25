<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
class Trivia extends Model
{
    use HasFactory;

    protected $table = 'trivias';

    protected $fillable = ['name' ,'category_id','game_type_id','game_mode_id','grand_price','point_eligibility', 'start_time', 'end_time'];
    protected $appends = [ 'is_active','has_played'];

    public function category(){
       return $this->belongsTo(Category::class);
    }

    public function triviaQuestions(){
        return $this->hasMany(TriviaQuestion::class);
    }

    public function gameSessions(){
        return $this->hasMany(GameSession::class);
    }

    public function getIsActiveAttribute()
    {
        if(($this->start_time <= Carbon::now('Africa/Lagos')) && 
        ($this->end_time > Carbon::now('Africa/Lagos')) ){
            return true;
        }

        return false;
    }

    public function getHasPlayedAttribute()
    {   
        $gameSession = $this->gameSessions()->where('user_id', auth()->user()->id)->first();
        
        if($gameSession === null){
            return false;
        }

        return true;
    }


}

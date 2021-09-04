<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameType extends Model
{
    use HasFactory;

    protected $fillable = ['name','description', 'instruction','category_id'];

    protected $appends = [
        'is_available'
     ];

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function getisAvailableAttribute()
    {   
        $hasQuestions = Question::where('game_type_id',$this->id)->first();
        
        if($hasQuestions === null){
            return false;
        }
        return true;

    }
}

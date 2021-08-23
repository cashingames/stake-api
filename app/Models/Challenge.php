<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    use HasFactory;

    protected $fillable = ["status", "user_id", "opponent_id", "category_id","game_type_id"];

    public function user(){
        return $this->belongsTo(User::class);
    }
}

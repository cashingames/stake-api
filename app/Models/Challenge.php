<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'user_id','opponent_id','status'];

    public function users(){
      
        return $this->belongsTo(User::class);
      
    }
}

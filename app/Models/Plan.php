<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = ['name' ,'description','price','game_count', 'background_color'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

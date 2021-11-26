<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = ['name' ,'description','price','game_count', 'background_color', 'is_free'];

    protected $casts = [
        'price' => 'integer',
        'is_free' => 'boolean'
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
   
    

}

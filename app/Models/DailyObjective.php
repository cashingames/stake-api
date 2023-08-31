<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyObjective extends Model
{
    use HasFactory;

    protected $fillable = ['objective_id', 'milestone_count', 'day'];

    public function objective()
    {
        return $this->belongsTo(Objective::class);
    }

    public function userDailyObjective()
    {
        return $this->hasMany(User::class, 'user_daily_objectives')->withPivot('count', 'is_achieved');
    }
}

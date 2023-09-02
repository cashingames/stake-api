<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Objective extends Model
{
    use HasFactory;

    protected $fillable = ['description', 'name', 'icon', 'reward', 'reward_type'];

    public function dailyObjectives()
    {
        return $this->hasMany(DailyObjective::class);
    }
}

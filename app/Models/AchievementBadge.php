<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AchievementBadge extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'milestone_type', 'milestone', 'reward_type', 'reward','description', 'medal'];

    public function userAchievementBadge()
    {
        return $this->hasMany(UserAchievementBadge::class);
    }
}

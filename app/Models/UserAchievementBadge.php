<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAchievementBadge extends Model
{
    use HasFactory;

    protected $fillable = ["user_id", "achievementbadge_id", "count", "is_claimed",  "created_at", "updated_at"];

    protected $casts = ['is_active' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function achievementBadge()
    {
        return $this->belongsTo(AchievementBadge::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'trigger',
        'life_span'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_rewards')->withPivot('reward_count', 'reward_date', 'reward_milestone', 'release_on');
    }

    public function rewardsBenefits()
    {
        return $this->hasMany(RewardBenefit::class);
    }
}

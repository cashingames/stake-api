<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class UserReward extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['user_id', 'reward_id', 'reward_count', 'reward_date', 'reward_milestone', 'release_on'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }
}

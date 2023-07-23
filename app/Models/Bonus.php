<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'trigger', 'duration_count','duration_measurement'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_bonuses')
            ->withPivot('is_on', 'amount_credited', 'amount_remaining_after_staking', 'total_amount_won')
            ->withTimestamps();
    }
}

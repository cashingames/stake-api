<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bonus_id',
        'is_on',
        'amount_credited',
        'amount_remaining_after_staking',
        'total_amount_won',
        'amount_remaining_after_withdrawal'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bonus()
    {
        return $this->belongsTo(Bonus::class);
    }
}

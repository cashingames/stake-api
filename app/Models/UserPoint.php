<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPoint extends Model
{
    use HasFactory;

    protected $fillable = ["user_id", "game_id", "description", "value", "point_flow_type", "point_aggregate"];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeToday($query): void
    {
        $query
            ->where('point_flow_type', 'POINTS_ADDED')
            ->where('created_at', '>=', now()->startOfDay());
    }

    public function getCurrentUserPoints($query): int
    {
        return $query
            ->where('user_id', auth()->user()->id)
            ->sum('value');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function scopeBetween($query, $startDate, $endDate): void
    {
        $query
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate);
    }

    public function scopeAddedBetween($query, $startDate, $endDate)
    {
        $query
            ->where('point_flow_type', 'POINTS_ADDED')
            ->between($startDate, $endDate);
    }
}

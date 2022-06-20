<?php

namespace App\Models;

use App\Traits\Utils\DateUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LiveTrivia extends Model
{
    use HasFactory, SoftDeletes, DateUtils;

    protected $table = 'trivias';

    protected $fillable = ['name', 'category_id', 'game_type_id', 'game_mode_id', 'grand_price', 'point_eligibility', 'start_time', 'end_time', 'is_published'];
    protected $casts = ['is_published' => 'boolean'];

    public function gameSessions()
    {
        return $this->hasMany(GameSession::class, 'trivia_id');
    }

    /**
     * Active  = 
     * Upcoming (Within X hours of running), 
     * Running, 
     * Closed (less than X hours after running)
     * Expired (X hours after closed)
     */
    public function scopeActive($query): void
    {
        $closedVisibilityDuration = config('trivia.live_trivia.display_shelf_life');
        $query
            ->where('is_published', true)
            ->where('end_time', '>=', now())
            ->orderBy('start_time', 'ASC');
    }

    public function scopeRecent($query): void
    {
        $query->where('is_published', true)
        ->orderBy('created_at', 'DESC');
    }
}

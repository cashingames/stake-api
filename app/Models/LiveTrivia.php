<?php

namespace App\Models;

use App\Traits\Utils\DateUtils;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiveTrivia extends Model
{
    use HasFactory, SoftDeletes, DateUtils;

    protected $table = 'trivias';

    protected $appends = ['status', 'start_time_utc', 'player_status'];
    protected $casts = ['is_published' => 'boolean'];

    public function gameSessions()
    {
        return $this->hasMany(GameSession::class);
    }

    public function getStartTimeUtcAttribute()
    {
        return $this->toTimestamp($this->start_time);
    }

    /**
     * @TODO: Point requirement check
     */
    public function getPlayerStatusAttribute()
    {
        $gameSession = $this->gameSessions()->where('user_id', auth()->user()->id)->first();

        if ($gameSession === null) {
            return "";
        }

        return "PLAYED";
    }


    public function getStatusAttribute()
    {
        $status = "";
        if (!$this->is_published) {
            return $status;
        }

        $start = Carbon::parse($this->start_time);
        $end =  Carbon::parse($this->end_time);

        if ($start > now()) {
            $status = "WAITING";
        } else if ($end > now()) {
            $status =  "ONGOING";
        } else if ($end->addHour(config('trivia.live_trivia.display_shelf_life')) >  now()) {
            $status =  "CLOSED";
        } else {
            $status =  "EXPIRED";
        }

        return $status;
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
}

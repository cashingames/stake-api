<?php

namespace App\Models;

use App\Traits\Utils\DateUtils;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\LiveTriviaPlayerStatus;
use App\Enums\LiveTriviaStatus;

class LiveTrivia extends Model
{
    use HasFactory, SoftDeletes, DateUtils;

    protected $table = 'trivias';

    protected $fillable = ['name', 'category_id', 'game_type_id', 'game_mode_id', 'grand_price', 'point_eligibility', 'start_time', 'end_time', 'is_published'];
    protected $appends = ['status', 'start_time_utc', 'player_status'];
    protected $casts = ['is_published' => 'boolean'];
   
    public function gameSessions()
    {
        return $this->hasMany(GameSession::class, 'trivia_id');
    }

    public function getStartTimeUtcAttribute()
    {
        return $this->toTimestamp($this->start_time);
    }


    public function getPlayerStatusAttribute()
    {
        $hasPlayed = $this->gameSessions()->where('user_id', auth()->user()->id)->exists();
        if ($hasPlayed) {
            return LiveTriviaPlayerStatus::Played;
        }

        $points = UserPoint::today()->where('user_id', auth()->user()->id)
            ->sum('value');

        if ($points < $this->point_eligibility) {
            return LiveTriviaPlayerStatus::LowPoints;
        }

        return LiveTriviaPlayerStatus::CanPlay;
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
            $status = LiveTriviaStatus::Waiting;
        } else if ($end > now()) {
            $status =  LiveTriviaStatus::Ongoing;
        } else if ($end->addHours(config('trivia.live_trivia.display_shelf_life')) >  now()) {
            $status =  LiveTriviaStatus::Closed;
        } else {
            $status =  LiveTriviaStatus::Expired;
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

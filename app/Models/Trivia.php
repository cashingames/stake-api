<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trivia extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trivias';

    protected $fillable = ['name', 'category_id', 'game_type_id', 'game_mode_id', 'grand_price', 'point_eligibility', 'start_time', 'end_time', 'is_published'];
    protected $appends = ['is_active', 'has_played', 'start_timespan', 'status'];
    protected $casts = ['is_published' => 'boolean'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function triviaQuestions()
    {
        return $this->hasMany(TriviaQuestion::class);
    }

    public function gameSessions()
    {
        return $this->hasMany(GameSession::class);
    }

    public function getIsActiveAttribute()
    {
        if ($this->is_published) {
            if (($this->start_time <= now()) &&
                ($this->end_time > now())
            ) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function getHasPlayedAttribute()
    {
        $gameSession = $this->gameSessions()->where('user_id', auth()->user()->id)->first();
        if ($gameSession === null) {
            return false;
        }
        return true;
    }

    public function getStartTimeSpanAttribute()
    {
        $start = Carbon::parse($this->start_time);
        if ($start >= Carbon::now()) {
            return $start->diffInMilliseconds(now());
        }
        return 0;
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
     * Scope a query to only include the most recent upcoming live trivia.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeUpcoming($query): void
    {
        $query
            ->where('is_published', true)
            ->where('start_time', '>=', Carbon::now())
            ->orderBy('start_time', 'ASC');
    }

    public function scopeActive($query): void
    {
        $closedVisibilityDuration = config('trivia.live_trivia.display_shelf_life');
        $query
            ->where('is_published', true)
            ->where('end_time', '>=', Carbon::now()->addHour($closedVisibilityDuration))
            ->orderBy('start_time', 'ASC');
    }
}

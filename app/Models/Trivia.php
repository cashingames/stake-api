<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
            if (($this->start_time <= Carbon::now('Africa/Lagos')) &&
                ($this->end_time > Carbon::now('Africa/Lagos'))
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
        if (Carbon::parse($this->start_time, 'Africa/Lagos') >= Carbon::now('Africa/Lagos')) {
            return Carbon::parse($this->start_time, 'Africa/Lagos')
                ->diffInMilliseconds(Carbon::now('Africa/Lagos'));
        }
        return 0;
    }

    public function getStatusAttribute()
    {
        /**
         * Status : 1) When a live trivia is published but start time has not reached 
         *              State = "UP COMING"
         *          2) When a live trivia is published and start time has reached but end time has not
         *              State = "RUNNING or IN PROGRESS"
         *          3) When a live trivia is published and start time and end time has passed
         *              State = "CLOSED"
         *          4) When a live trivia is published and is closed , but end time is X-time ago
         *              State = "EXPIRED"
         */
        if ($this->is_published) {
            if (($this->start_time > Carbon::now('Africa/Lagos')) &&
                ($this->end_time > Carbon::now('Africa/Lagos'))
            ) {
                return "UP_COMING";
            }
            if (($this->start_time <= Carbon::now('Africa/Lagos')) &&
                ($this->end_time > Carbon::now('Africa/Lagos'))
            ) {
                return "RUNNING";
            }
            if (($this->start_time <= Carbon::now('Africa/Lagos')) &&
                ($this->end_time <= Carbon::now('Africa/Lagos'))
            ) {
                if (Carbon::parse($this->end_time, 'Africa/Lagos')->addHour(config('trivia.live_trivia.display_shelf_life')) 
                <= Carbon::now('Africa/Lagos')) {
                    return "EXPIRED";
                }
                return "CLOSED";
            }
        }
        return "UNPUBLISHED";
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
            ->where('start_time', '>=', Carbon::now('Africa/Lagos'))
            ->orderBy('start_time', 'ASC');
    }

    public function scopeOngoingLiveTrivia($query): void
    {
        $query
            ->where('is_published', true)
            ->where('end_time', '>=', Carbon::now('Africa/Lagos')->addHour(1))
            ->orderBy('start_time', 'ASC');
    }
}

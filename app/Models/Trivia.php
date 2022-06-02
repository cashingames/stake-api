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
    protected $appends = ['is_active', 'has_played', 'start_timespan'];
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlineTimeline extends Model
{
    use HasFactory;

    protected $fillable = ['referrer','user_id'];

    protected $appends = [
        'is_currently_online'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function getIsCurrentlyOnlineAttribute()
    {
        if($this->updated_at >Carbon::now()->subMinutes(5)->toDateTimeString()){
            return true;
        }
        return false;
    }
}

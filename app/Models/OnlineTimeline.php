<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlineTimeline extends Model
{
    use HasFactory;

    protected $fillable = ['referrer','user_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }
}

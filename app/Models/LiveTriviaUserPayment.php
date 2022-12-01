<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveTriviaUserPayment extends Model
{
    use HasFactory;

    protected $fillable = [
       'trivia_id',
       'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function liveTrivia()
    {
        return $this->belongsTo(Trivia::class);
    }
}

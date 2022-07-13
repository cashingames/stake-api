<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeQuestion extends Model
{
    use HasFactory;


    protected $fillable = ['question_id', 'challenge_id'];

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}

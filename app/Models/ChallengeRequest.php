<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeRequest extends Model
{
    use HasFactory;

    protected $fillable = ['challenge_request_id', 'user_id','username','amount','category_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function questions()
    {
        return $this->hasMany(TriviaChallengeQuestion::class);
    }

    public function scopeToBeCleanedUp($query){
        return $query->where('status', 'MATCHING')
        ->where('created_at','<=',now()->subMinutes(2));
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryRanking extends Model
{
    use HasFactory;

    protected $fillable = ["user_id", "category_id", "points_gained"];

    protected $appends = [
        'user_profile'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUserProfileAttribute()
    {
        $user = User::where('id', $this->user_id)->first();

        return $user->load('profile');
    }
}

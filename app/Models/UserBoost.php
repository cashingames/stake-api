<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserBoost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'boost_id',
        'boost_count',
        'used_count'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function boost()
    {
        return $this->belongsTo(Boost::class);
    }
}

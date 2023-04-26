<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCoin extends Model
{
    use HasFactory;

    protected $fillable =[
        'coins_value'
    ];
    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCoinTransaction extends Model
{
    use HasFactory;

    protected $fillable =[
        'value',
        'user_id',
        'transaction_type',
        'description',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

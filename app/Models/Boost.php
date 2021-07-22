<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boost extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'point_value', 'currency_value'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

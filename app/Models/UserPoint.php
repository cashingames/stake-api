<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPoint extends Model
{
    use HasFactory;

    protected $fillable = ["source","value"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

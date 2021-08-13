<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPoint extends Model
{
    use HasFactory;

    protected $fillable = ["user_id","game_id","description","value","point_flow_type", "point_aggregate"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

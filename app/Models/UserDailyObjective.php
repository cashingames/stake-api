<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserDailyObjective extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['user_id', 'daily_objective_id', 'count', 'is_achieved'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function daiyObjective()
    {
        return $this->belongsTo(DailyObjective::class);
    }
}

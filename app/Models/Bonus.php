<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'trigger', 'duration_count','duration_measurement'];

    public function userBonuses()
    {
        return $this->hasMany(UserBonus::class);
    }
}

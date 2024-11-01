<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StakingOdd extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['score', 'odd', 'active', 'module'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at', 'module', 'active'];
    protected $table = "staking_odds";

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}

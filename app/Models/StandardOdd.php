<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StandardOdd extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['score', 'odd', 'active'];

    protected $table = "standard_odds";

    public function scopeActive($query){
        return $query->where('active', true);
    }
}

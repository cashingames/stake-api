<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Boost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description', 'point_value', 'currency_value', 'pack_count', 'icon'];

    public function getPointValueAttribute($value){
       return round(($value * $this->pack_count),1);
    }

    public function getCurrencyValueAttribute($value){
        return round(($value * $this->pack_count),1);
     }

}

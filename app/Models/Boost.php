<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boost extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'point_value', 'currency_value', 'pack_count'];

    public function getPointValueAttribute($value){
       return round(($value * $this->pack_count),1);
    }

    public function getCurrencyValueAttribute($value){
        return round(($value * $this->pack_count),1);
     }

}

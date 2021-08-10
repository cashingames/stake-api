<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boost extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'point_value', 'currency_value', 'pack_count'];

}

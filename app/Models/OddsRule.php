<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OddsRule extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = "odds_rules";
}

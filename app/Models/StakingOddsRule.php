<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StakingOddsRule extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = "staking_odds_rules";
}

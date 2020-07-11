<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    //
    protected $fillable = [
        "id", "user_id", "referral_code"
    ];

}

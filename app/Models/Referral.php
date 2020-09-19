<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Referral extends Model
{
  use HasFactory;
  
  protected $fillable = [
      "id", "user_id", "referral_code"
  ];

}
 
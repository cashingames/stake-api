<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserVoucher extends Model
{
  use HasFactory;

  protected $table = 'user_vouchers';

  protected $fillable = [
      'user_id', 'voucher_id', 'unit_used', 
  ];
}

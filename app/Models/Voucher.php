<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Voucher extends Model
{
  use HasFactory;

  protected $fillable = [
      'code', 'expire', 'count', 'unit', 'type',
  ];

  public function user(){
      return $this->belongsTo(User::class);
  }
}

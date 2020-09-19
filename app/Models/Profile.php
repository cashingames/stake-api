<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Profile extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
      'first_name', 'last_name', 'gender', 'date_of_birth', 'address', 'state', 'referral_code',
  ];

  //
  public function user(){
      return $this->belongsTo(User::class);
  }

  public function getAvatarAttribute($value)
  {
      if( is_null($value) || $value == "")
          return "";
      return asset('avatar/'.$value."?".rand());
  }

  public function getGenderAttribute($value){
      return $value??'';
  }

  public function getStateAttribute($value){
      return $value??'';
  }
}

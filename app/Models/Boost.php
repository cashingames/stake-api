<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Boost extends Model
{
   use HasFactory, SoftDeletes;

   protected $fillable = ['name', 'description', 'point_value', 'currency_value', 'pack_count', 'icon'];

   protected $appends = ['price'];

   public function users(): BelongsToMany
   {
      return $this->belongsToMany(User::class, 'user_boosts')
         ->withPivot('boost_count', 'used_count')
         ->withTimestamps();
   }

   public function getPriceAttribute()
   {
      return round($this->pack_count * $this->currency_value, 1);
   }

}
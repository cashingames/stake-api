<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Voucher extends Model
{
    //

    protected $fillable = [
        'code', 'expire', 'count', 'unit', 'type',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use App\User;
use App\WalletTransaction;

class Wallet extends Model
{
    //

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'bonus', 'cash', 'bonus', 'user_id', 'balance'
    ];


    public function owner(){
        return $this->belongsTo(User::class);
    }

    public function transactions(){
        return $this->hasMany(WalletTransaction::class);
    }
}

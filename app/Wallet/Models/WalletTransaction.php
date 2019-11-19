<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Wallet;

class WalletTransaction extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'wallet_id', 'transaction_type', 'amount', 'description', 'reference',
    ];


    public function wallet(){
        return $this->belongsTo(Wallet::class);
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Wallet;

class WalletTransaction extends Model
{

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    // protected $dispatchesEvents = [
    //     'created' => WalletUpdated::class,
    // ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'wallet_id', 'transaction_type', 'amount', 'description', 'reference', 'balance', 'wallet_type',
    ];


    protected static function boot()
    {
        parent::boot();
        WalletTransaction::created(function ($model) {
            Wallet::changeWalletBalance($model);
        });

        // WalletTransaction::creating(function ($model) {
        //     WalletTransaction::setBalance($model);
        // });
    }

    // protected static function setBalance(WalletTransaction $transaction){

    // }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    // public function getFirstNameAttribute($value)
    // {
    //     return ucfirst($value);
    // }

}

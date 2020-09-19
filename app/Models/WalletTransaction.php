<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
  use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'wallet_id', 'transaction_type', 'amount', 'description', 'reference', 'balance', 'wallet_type','wallet_kind'
    ];


    protected static function boot()
    {
        parent::boot();

        //@TODO: This operation is expensive
        WalletTransaction::created(function ($model) {
            Wallet::changeWalletBalance($model);
        });

    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

}

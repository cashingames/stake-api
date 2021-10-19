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
        'wallet_id', 'transaction_type', 'amount', 'description', 'reference', 'balance'
    ];


    protected $hidden = [
        'laravel_through_key'
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public function getTransactionTypeAttribute($value)
    {
        if ($value == "CREDIT") {
            return "Fund Received";
        } else if ($value == "DEBIT") {
            return "Fund Withdrawal";
        }
    }
}

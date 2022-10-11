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
        'wallet_id', 'transaction_type', 'amount', 'description', 'reference', 'balance','viable_date','settled_at'
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

    public function scopeUnsettled($query){
        
        return $query->where('transaction_type', 'CREDIT')
        ->whereNull('settled_at')->whereNotNull('viable_date');
    }

    public function scopeWithdrawals($query){
        return $query->where('transaction_type', 'DEBIT')
        ->where('description','Winnings Withdrawal Made');
    }
}

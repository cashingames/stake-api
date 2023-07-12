<?php

namespace App\Models;

use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WalletTransaction extends Model
{
    use HasFactory, SoftDeletes;

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
        'wallet_id', 'transaction_type', 'amount', 'description', 'reference', 'balance', 'transaction_action','balance_type',
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

    public function scopeWithdrawals($query)
    {
        return $query->where('transaction_type', 'DEBIT')
            ->where('transaction_action', WalletTransactionAction::WinningsWithdrawn->value);
    }

    public function scopeTotalFundings($query)
    {
        return $query->where('transaction_type', 'CREDIT')
        ->where('transaction_action', WalletTransactionAction::WalletFunded->value);;
    }

    public function scopeMainTransactions($query)
    {
        return $query->where('balance_type',  WalletBalanceType::CreditsBalance->value);
    }

    public function scopeBonusTransactions($query)
    {
        return $query->where('balance_type',  WalletBalanceType::BonusBalance->value);
    }

    public function scopeWinningsTransactions($query)
    {
        return $query->where('balance_type', WalletBalanceType::WinningsBalance->value);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform_account',
        'withdrawable_account', 
        'user_id', 
        'balance'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    protected static function changeWalletBalance(WalletTransaction $model)
    {
        $wallet = $model->wallet;
        if ($model->transaction_type == "Fund Recieved" && $model->wallet_kind == "CREDITS") {
            $wallet->platform_account += $model->amount;
        } else if ($model->transaction_type == "Fund Recieved" && $model->wallet_kind == "WINNINGS") {
            $wallet->withdrawable_account += $model->amount;
        } else if ($model->transaction_type == "Fund Withdrawal" && $model->wallet_kind == "CREDITS") { 
            //Subtract amount from credits  
            $wallet->platform_account -= $model->amount;
        } else if ($model->transaction_type == "Fund Withdrawal" && $model->wallet_kind == "WINNINGS"){
            //Subtract amount from winnings
            $wallet->withdrawable_account -= $model->amount;
        }

        $wallet->balance = $wallet->platform_account + $wallet->withdrawable_account;
        $model->balance = $wallet->balance;

        $wallet->update();
        $model->update();
    }


}

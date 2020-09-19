<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wallet extends Model
{
  use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account1','account2', 'user_id', 'balance'
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
        if ($model->transaction_type == "CREDIT" && $model->wallet_kind == "CREDITS") {
            $wallet->account1 += $model->amount;
        } else if ($model->transaction_type == "CREDIT" && $model->wallet_kind == "WINNINGS") {
            $wallet->account2 += $model->amount;
        } else if ($model->transaction_type == "DEBIT" && $model->wallet_kind == "CREDITS") { 
            //Subtract amount from credits  
            $wallet->account1 -= $model->amount;
        } else if ($model->transaction_type == "DEBIT" && $model->wallet_kind == "WINNINGS"){
            //Subtract amount from winnings
            $wallet->account2 -= $model->amount;
        }

        $wallet->balance = $wallet->account1 + $wallet->account2;
        $model->balance = $wallet->balance;

        $wallet->update();
        $model->update();
    }


}

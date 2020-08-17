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
        'credits','winnings', 'user_id', 'balance'
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
        if ($model->transaction_type == "CREDIT" && $model->wallet_type == "CREDITS") {
            $wallet->credits += $model->amount;
        } else if ($model->transaction_type == "CREDIT" && $model->wallet_type == "WINNINGS") {
            $wallet->winnings += $model->amount;
        } else if ($model->transaction_type == "DEBIT" && $model->wallet_type == "CREDITS") { 
            //Subtract amount from credits  
            $wallet->credits -= $model->amount;
        } else if ($model->transaction_type == "DEBIT" && $model->wallet_type == "WINNINGS"){
            //Subtract amount from winnings
            $wallet->winnings -= $model->amount;
        }

        $wallet->balance = $wallet->credits + $wallet->winnings;
        $model->balance = $wallet->balance;

        $wallet->update();
        $model->update();
    }


}

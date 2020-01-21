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
        if ($model->transaction_type == "CREDIT" && $model->wallet_type == "BONUS") {
            $wallet->bonus += $model->amount;
        } else if ($model->transaction_type == "CREDIT" && $model->wallet_type == "CASH") {
            $wallet->cash += $model->amount;
        } else if ($model->transaction_type == "DEBIT") {

            //find first remove from bonus if it has balance
            //then remove the remaining from cash
            $remain = $wallet->bonus - $model->amount;
            if($wallet->bonus == 0.00 || $remain < 0.00){
                $wallet->cash -= \abs($remain);
            }

            if($wallet->bonus > 0.00 && $remain < 0){
                $wallet->bonus = 0;
            }

            if($remain >= 0.00){
                $wallet->bonus -= $model->amount;
            }



        }

        $wallet->balance = $wallet->bonus + $wallet->cash;
        $model->balance = $wallet->balance;

        $wallet->update();
        $model->update();
    }


}

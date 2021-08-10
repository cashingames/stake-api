<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
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
        if ($model->transaction_type == "Fund Recieved" ) {
            $wallet->balance += $model->amount;
        } 
        if ($model->transaction_type == "Fund Withdrawal" ) {  
            $wallet->balance -= $model->amount;
        } 
        
        $model->balance = $wallet->balance;

        $wallet->update();
        $model->update();
    }


}

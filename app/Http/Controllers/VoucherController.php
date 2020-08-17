<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Voucher;
use App\User;
use Carbon\Carbon;
use App\UserVoucher;
use App\WalletTransaction;
use Illuminate\Support\Str;

class VoucherController extends BaseController
{
  
    public function consume ($code){

        //Check if code exists 
        //If code does not exist, respond 'Code does not exist'
        //If code exists,
        //Get the current time with Carbon
        //Get the code's expiry time with Carbon (Carbon helps for easy comparison of date and time)
        //Get the number of times the voucher has been used
        //If current time is greater than code expiry time
        //send error response your code has expired
        //If the number of times code has been used is  equal to the code count
        //Send error response Your code limit has been exhausted.
        //Save record to user_voucher table
        //Then select the bonus of the User from Wallet
        //Get the new balance of the user by adding User's current bonus to voucher code's unit
        //Update the user's bonus  with credit.
        //Send success response.
        
        /***********************************************/

        //checking if code exists
        $voucher = Voucher::firstWhere('code', $code);
        if($voucher == null){
            return $this->sendError("This code does not exist", "This code does not exist");
        } 
        
        //Get the expiry time with Carbon 
        $expiryToCarbon= new Carbon($voucher->expire);
        //If current time is greater than code expiry time
        if(Carbon::now() > $expiryToCarbon){
            return $this->sendError("Sorry, your voucher code has expired!", "Sorry, your voucher code has expired!");
        }

        
        //Get the number of times the voucher has been used
        $codeCount = UserVoucher::where('voucher_id',$voucher->id)->count();
        
        //If the number of times code has been used  equal to the code count
        if($codeCount == $voucher->count){
            return $this->sendError("Sorry, your voucher limit has been exhausted!", "Sorry, your voucher limit has been exhausted!");
        } 

        //This saves the record in the user voucher table
        $userVoucher = new UserVoucher;
        $userVoucher->user_id = $this->user->id;
        $userVoucher->voucher_id = $voucher->id;
        $userVoucher->save();
       
        //Update the user's bonus with new balance.
       
        WalletTransaction::create([
            'wallet_id' => $this->user->wallet->id,
            'transaction_type' => 'CREDIT',
            'amount' =>  ($this->user->wallet->credits + $voucher->unit) - $this->user->wallet->credits ,
            'wallet_type' => 'CREDITS',
            'description' => 'Credit from voucher used',
            'reference' => Str::random(10)
        ]);
        
        //Return success message
        $this->user->wallet->refresh();
        return $this->sendResponse($this->user->wallet, "Your wallet has been credited with " . $voucher->unit ." units.");                 
    
    
    }

}

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
    // /**
    //  * Validates that a coupon is valid
    //  * and consume it if it is
    //  */
    // public function validate(string $code){
        
    // }

    private function checkCode($code){
        if ( Voucher::where('code', $code)->exists()){
           $codeExists = true;
        } else {
            $codeExists = false;
        }
        return $codeExists;
    }

    public function generate(){
    //Specify the number of coupons to be generated at a go (100 maybe?)\
    // Specify the length each coupon will be
    // Specify the selected characters for the coupons
    /* From the selected characters, generate 100 random combinations of the characters not greater 
    than the  length of each coupon
    */
    //check if code already exists
        //if code exists, generate new code
        //else
    // Save the generated coupons in the vouchers table
    
    
        for ($i = 0; $i <= 100; $i++){
            $length = 10;
            $characters = "123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ";
            $code = substr(str_shuffle($characters), 0, $length);
            //   echo $code;
            //   die();
            $checkCode = $this->checkCode($code);

            if($checkCode == true){
                $code = substr(str_shuffle($characters), 0, $length); 
                $checkCode = $this->checkCode($code);
            }

            //save to database
            $voucher = new Voucher;
            $voucher->code = $code;
            $voucher->count = rand(1 , 3);
            $voucher->expire = now()->addDays(1);
            $voucher->unit = rand(150,2000);
            $voucher->type='cash';
            $voucher->save();

            return $this->sendResponse($voucher, "100 Coupons generated ");
        }
    }

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
            'amount' =>  $this->user->wallet->bonus + $voucher->unit,
            'wallet_type' => 'BONUS',
            'description' => 'Credit from voucher used',
            'reference' => Str::random(10)
        ]);
        
        //Return success message
        $this->user->wallet->refresh();
        return $this->sendResponse($this->user->wallet, "Your wallet has been credited with " . $voucher->unit ." units.");
        

                        
    
    
    }

}

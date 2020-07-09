<?php

namespace App\Http\Controllers;

use App\Game;
use App\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends BaseController
{


    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth:api');
    // }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = $this->user->load('profile');
        $result = [
            'user' => $user,
            'plans' => $user->activePlans()->get(),
            'wallet' => $user->wallet
        ];
        return $this->sendResponse($result, 'User details');
    }

    public function plans()
    {
        $myPlans = $this->user->activePlans()->get();
        return $this->sendResponse($myPlans, 'User active plans');
    }

    public function logError(Request $request){
        Log::error($request->data);
        return "";
    }

    public function generateReferralCode(){
        // Specify the length the code will be
        // Specify the selected characters for the coupons
        /* From the selected characters, generate a random combination of the characters not greater 
        than the  length of each coupon
        */
        //check if code exists
        //if code does not exist
        // Save the generated coupons in the referrals table

        $length = 10;
        $characters = "123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ";
        $referralCode = substr(str_shuffle($characters), 0, $length);
        
        //check if code exists
        $codeExists= Referral::where('referral_code', $referralCode)->exists();

        if ($codeExists !== null){
            $referralCode = substr(str_shuffle($characters), 0, $length);
        }

        //save to database
        $referral = new Referral;
        $referral->referral_code = $referralCode;
        $referral->user_id = $this->user->id;
        $referral->save();

        return $this->sendResponse($referral, "Referral code generated.");
    }
}

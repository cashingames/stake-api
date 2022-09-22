<?php

namespace App\Http\Controllers;

use App\Services\Payments\PaystackWithdrawalService;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WithdrawWinningsController extends BaseController
{
    //

    public function __invoke(Request $request)
    {
        // $data = $request->validate([
        //     'amount' => ['required', 'numeric', 'min:0'],
        // ]);

        if (is_null($this->user->profile->bank_name) || is_null($this->user->profile->account_number)) {
            return $this->sendError(false, 'Please update your profile with your bank details');
        }

        $debitAmount = $this->user->wallet->withdrawable_balance;// $data['amount'];

        // if ($this->user->wallet->withdrawable_balance < $debitAmount) {
        //     return $this->sendError(false, 'Insufficient Balance');
        // }
        
        $paystackWithdrawal = new PaystackWithdrawalService(config('trivia.payment_key'));

        
        $banks = Cache::get('banks');
        
        if (is_null($banks)) {
            $banks = $paystackWithdrawal->getBanks();
        }

        
        $bankCode = '';

        foreach ($banks->data as $bank) {
            if ($bank->name == $this->user->profile->bank_name) {
                $bankCode = $bank->code;
            }
        }

        $isValidAccount = $paystackWithdrawal->verifyAccount($bankCode);

        if (!$isValidAccount) {
            return $this->sendError(false, 'Account is not valid');
        }
        $recipientCode = $paystackWithdrawal->createTransferRecipient($bankCode);

        if (is_null($recipientCode)) {
            return $this->sendError(false, 'Recipient code could not be generated');
        }

        $isTransferInitiated = $paystackWithdrawal->initiateTransfer($recipientCode, ($debitAmount * 100));

        
        if($isTransferInitiated === 'pending'){
            /**
             * Webhook implemented in wallet controller handles 
             * listening for successful transfer and 
             * updating of user transaction */
            return $this->sendResponse(true, "Transfer processing, wait for your bank account to reflect");
        }
        if ($isTransferInitiated === "success"){
            return $this->sendResponse(true, "Your transfer is being successfully processed to your bank account");
        }
    }
}

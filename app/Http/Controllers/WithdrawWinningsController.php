<?php

namespace App\Http\Controllers;

use App\Models\WalletTransaction;
use App\Services\Payments\PaystackService;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawWinningsController extends BaseController
{
    //
    //@TODO - enforce hours between which withdrawals can be made
    public function __invoke(Request $request, PaystackService $withdrawalService)
    {

        if (is_null($this->user->profile->bank_name) || is_null($this->user->profile->account_number)) {
            return $this->sendError(false, 'Please update your profile with your bank details');
        }

        //@TODO Uncomment when the frontend is ready
        $todaysWithdrawals = $this->user->transactions()->withdrawals()->where('wallet_transactions.created_at', '>=', now()->startOfDay())->sum('amount');
        
        if (is_null($this->user->email_verified_at) && $todaysWithdrawals > config('trivia.max_withdrawal_amount')) {
            return $this->sendError(false, 'Please verify your email address to make withdrawals  or contact support on hello@cashingames.com');
        }

        // if (is_null($this->user->phone_verified_at)) {
        //     return $this->sendError(false, 'Please verify your phone number to make withdrawals or contact support on hello@cashingames.com');
        // }

        $debitAmount = $this->user->wallet->withdrawable_balance;

        if ($debitAmount <= 0) {
            return $this->sendError(false, 'Invalid withdrawal amount. You can not withdraw NGN0');
        }

        if ($debitAmount < config('trivia.min_withdrawal_amount')) {
            return $this->sendError(false, 'You can not withdraw less than NGN' . config('trivia.min_withdrawal_amount'));
        }

        if ($debitAmount > config('trivia.max_withdrawal_amount')) {
            $debitAmount = config('trivia.max_withdrawal_amount');
        }

        $banks = Cache::get('banks');

        if (is_null($banks)) {
            $banks = $withdrawalService->getBanks();
        }


        $bankCode = '';

        foreach ($banks->data as $bank) {
            if ($bank->name == $this->user->profile->bank_name) {
                $bankCode = $bank->code;
            }
        }

        $isValidAccount = $withdrawalService->verifyAccount($bankCode);

        if (!$isValidAccount) {
            return $this->sendError(false, 'Account is not valid');
        }
        $recipientCode = $withdrawalService->createTransferRecipient($bankCode);

        if (is_null($recipientCode)) {
            return $this->sendError(false, 'Recipient code could not be generated');
        }

        Log::info($this->user->username . " requested withdrawal of {$debitAmount}");

        try {
            $transferInitiated = $withdrawalService->initiateTransfer($recipientCode, ($debitAmount * 100));
        } catch (\Throwable $th) {
            return $this->sendError(false, "We are unable to complete your withdrawal request at this time, please try in a short while or contact support");
        }

        DB::transaction(function () use ($transferInitiated, $debitAmount) {
            $this->user->wallet->withdrawable_balance -= $debitAmount;

            WalletTransaction::create([
                'wallet_id' => $this->user->wallet->id,
                'transaction_type' => 'DEBIT',
                'amount' => $debitAmount,
                'balance' => $this->user->wallet->withdrawable_balance,
                'description' => 'Winnings Withdrawal Made',
                'reference' => $transferInitiated->reference,
            ]);
            $this->user->wallet->save();
        });

        Log::info('withdrawal transaction created ' . $this->user->username);

        if ($transferInitiated->status === 'pending') {
            return $this->sendResponse(true, "Transfer processing, wait for your bank account to reflect");
        }
        if ($transferInitiated->status === "success") {
            return $this->sendResponse(true, "Your transfer is being successfully processed to your bank account");
        }
    }
}

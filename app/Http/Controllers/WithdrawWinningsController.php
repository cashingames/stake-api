<?php

namespace App\Http\Controllers;

use App\Models\WalletTransaction;
use App\Services\Payments\PaystackWithdrawalService;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WithdrawWinningsController extends BaseController
{
    //

    public function __invoke(Request $request, PaystackWithdrawalService $withdrawalService)
    {
        // $data = $request->validate([
        //     'amount' => ['required', 'numeric', 'min:0'],
        // ]);

        if (is_null($this->user->profile->bank_name) || is_null($this->user->profile->account_number)) {
            return $this->sendError(false, 'Please update your profile with your bank details');
        }

        $debitAmount = $this->user->wallet->withdrawable_balance; // $data['amount'];

        if ($debitAmount <= 0) {
            return $this->sendError(false, 'Invalid withdrawal amount. You can not withdraw NGN0');
        }

        $totalAmountWithdrawn = $this->user->transactions()->withdrawals()->whereBetween('wallet_transactions.created_at', [now()->subDays(config('trivia.staking.total_withdrawal_days_limit')), now()])->sum('amount');

        if ($totalAmountWithdrawn >= config('trivia.staking.total_withdrawal_limit')) {
            return $this->sendError(false, 'you cannot withdaw more than NGN' . config('trivia.staking.total_withdrawal_limit') . ' in ' . config('trivia.staking.total_withdrawal_days_limit') . ' days');
        }

        if ($debitAmount < config('trivia.staking.min_withdrawal_amount')) {
            return $this->sendError(false, 'You can not withdraw less than NGN' . config('trivia.staking.min_withdrawal_amount'));
        }
        if ($debitAmount > config('trivia.staking.max_withdrawal_amount')) {
            $debitAmount = config('trivia.staking.max_withdrawal_amount');
            // dd( $debitAmount);
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
            //throw $th;
            return $this->sendError(false, "We are unable to complete your withdrawal request at this time, please try in a short while or contact support");
        }

        $this->user->wallet->withdrawable_balance -= $debitAmount;
        $this->user->wallet->save();

        WalletTransaction::create([
            'wallet_id' => $this->user->wallet->id,
            'transaction_type' => 'DEBIT',
            'amount' => $debitAmount,
            'balance' => $this->user->wallet->withdrawable_balance,
            'description' => 'Winnings Withdrawal Made',
            'reference' => $transferInitiated->reference,
        ]);

        Log::info('withdrawal transaction created ' . $this->user->username);

        if ($transferInitiated->status === 'pending') {
            /**
             * Webhook implemented in wallet controller handles 
             * listening for successful transfer and 
             * updating of user transaction */
            return $this->sendResponse(true, "Transfer processing, wait for your bank account to reflect");
        }
        if ($transferInitiated->status === "success") {
            // if ($debitAmount == config('trivia.staking.max_withdrawal_amount')) {
            //     return $this->sendResponse(true, "NGN" . config('trivia.staking.max_withdrawal_amount') . " is being successfully processed to your bank account.");
            // }
            return $this->sendResponse(true, "Your transfer is being successfully processed to your bank account");
        }
    }
}

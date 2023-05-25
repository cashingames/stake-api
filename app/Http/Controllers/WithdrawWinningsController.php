<?php

namespace App\Http\Controllers;

use App\Enums\ClientPlatform;
use App\Enums\WalletTransactionAction;
use App\Models\WalletTransaction;
use App\Repositories\Cashingames\WalletRepository;
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
    public function __invoke(Request $request, PaystackService $withdrawalService, WalletRepository $walletRepository)
    {
        $request->validate([
            'account_number' => ['required', 'numeric'],
            'bank_name' => ['required', 'string', 'max:200'],
            'amount' => ['required','integer', 'max:' . $this->user->wallet->withdrawable],
            'account_name' => ['required', 'string']
        ]);

        $totalWithdrawals = $this->user->transactions()->withdrawals()->sum('amount');

        if (is_null($this->user->email_verified_at) && $totalWithdrawals > config('trivia.email_verification_limit_threshold')) {
            $data = [
                'verifyEmailNavigation' => true,
            ];
            return $this->sendError($data, 'Please verify your email address to make withdrawals  or contact support on hello@cashingames.com');
        }

        $debitAmount = $request->amount;


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
            if ($bank->name == $request->bank_name) {
                $bankCode = $bank->code;
            }
        }

        $verifyAccount = $withdrawalService->verifyAccount($bankCode, $request->account_number);

        if (!$verifyAccount->status) {
            return $this->sendError(false, 'Account is not valid');
        }

        $fullName = $this->user->profile->first_name . ' ' . $this->user->profile->last_name;

        $verifiedAccountName = $this->getValidAccountName($verifyAccount->data->account_name);

        if (
            ($verifiedAccountName['firstAndLastName'] !== strtoupper($fullName) )
        ) {
            Log::info($this->user->username . " valid account names are {$verifiedAccountName['firstAndLastName']} and {$verifiedAccountName['lastAndFirstName']} ");
            return $this->sendError(false, 'Account name does not match your registration name. Please contact support.');
        }

        $recipientCode = $withdrawalService->createTransferRecipient($bankCode, $verifyAccount->data->account_name, $request->account_number);

        if (is_null($recipientCode)) {
            return $this->sendError(false, 'Recipient code could not be generated');
        }

        Log::info($this->user->username . " requested withdrawal of {$debitAmount}");

        try {
            $transferInitiated = $withdrawalService->initiateTransfer($recipientCode, ($debitAmount * 100));
        } catch (\Throwable $th) {
            return $this->sendError(false, "We are unable to complete your withdrawal request at this time, please try in a short while or contact support");
        }

        $walletRepository->debit($this->user->wallet,  $debitAmount, 'Winnings Withdrawal Made', null, "withdrawable", WalletTransactionAction::WinningsWithdrawn->value);

        Log::info('withdrawal transaction created ' . $this->user->username);

        if ($transferInitiated->status === 'pending') {
            return $this->sendResponse(true, "Transfer processing, wait for your bank account to reflect");
        }
        if ($transferInitiated->status === "success") {
            return $this->sendResponse(true, "Your transfer is being successfully processed to your bank account");
        }
    }

    private function getValidAccountName($accountName)
    {
        $accountNameParts = explode(" ", $accountName);
        $firstAndLastName = $accountNameParts[0] . " " . $accountNameParts[count($accountNameParts) - 1];
        $lastAndFirstName = $accountNameParts[count($accountNameParts) - 1] . " " . $accountNameParts[0];
        return [
            'firstAndLastName' => $firstAndLastName,
            'lastAndFirstName' => $lastAndFirstName
        ];
    }
}

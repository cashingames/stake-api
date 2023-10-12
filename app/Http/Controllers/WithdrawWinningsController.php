<?php

namespace App\Http\Controllers;

use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Enums\WalletTransactionType;
use App\Jobs\SendAdminErrorEmailUpdate;
use App\Repositories\Cashingames\WalletRepository;
use App\Repositories\Cashingames\WalletTransactionDto;
use App\Services\Payments\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WithdrawWinningsController extends BaseController
{

    public function __construct(
        private readonly WalletRepository $walletRepository,
        private readonly PaystackService $withdrawalService,
    ) {
        parent::__construct();
        $this->middleware('auth:api');
    }
    public function __invoke(
        Request $request,
    ) {
        $request->validate([
            'account_number' => ['required', 'numeric'],
            'bank_name' => ['required', 'string', 'max:200'],
            'amount' => [
                'required',
                'integer',
                'max:' . $this->user->wallet->withdrawable,
                'min:' . config('trivia.min_withdrawal_amount')
            ],
        ]);

        $kycResult = $this->checkKYC();
        if (!is_null($kycResult)) {
            return $kycResult;
        }

        $debitAmount = $request->amount;

        $banks = Cache::rememberForever('banks', function () {
            return $this->withdrawalService->getBanks();
        });
        $bankCode = '';
        foreach ($banks->data as $bank) {
            if ($bank->name == $request->bank_name) {
                $bankCode = $bank->code;
                break;
            }
        }

        $verifyAccount = $this->withdrawalService->verifyAccount($bankCode, $request->account_number);

        if (!$verifyAccount->status) {
            return $this->sendError(false, 'Account is not valid');
        }

        $validateAccountName = $this->validateAccountName($verifyAccount->data->account_name);
        if (!is_null($validateAccountName)) {
            return $validateAccountName;
        }

        $recipientCode = $this->withdrawalService->createTransferRecipient(
            $bankCode,
            $verifyAccount->data->account_name,
            $request->account_number
        );

        if (is_null($recipientCode)) {
            return $this->sendError(false, 'Recipient code could not be generated');
        }

        Log::info($this->user->username . " requested withdrawal of {$debitAmount}");

        try {
            $transferInitiated = $this->withdrawalService->initiateTransfer($recipientCode, ($debitAmount * 100));
        } catch (\Throwable $th) {
            return $this->sendError(
                false,
                "We are unable to complete your withdrawal request at this time, " .
                "please try in a short while or contact support"
            );
        }

        $this->walletRepository->addTransaction(
            new WalletTransactionDto(
                $this->user->id,
                $debitAmount,
                'Successful Withdrawal',
                WalletBalanceType::WinningsBalance,
                WalletTransactionType::Debit,
                WalletTransactionAction::WinningsWithdrawn
            )
        );

        Log::info('withdrawal transaction created ' . $this->user->username);

        if ($transferInitiated->status == 'pending') {
            return $this->sendResponse(true, "Transfer processing, wait for your bank account to reflect");
        }
        if ($transferInitiated->status == "success") {
            return $this->sendResponse(true, "Your transfer is being successfully processed to your bank account");
        }
    }

    private function validateAccountName($bankAccountName)
    {
        $suppliedNameArr = explode(' ', $this->cleanName($this->user->profile->full_name));
        $bankAccountNamesArr = explode(' ', $this->cleanName($bankAccountName));

        $countOfFoundName = 0;
        foreach ($suppliedNameArr as $name) {
            if (in_array($name, $bankAccountNamesArr)) {
                $countOfFoundName++;
            }
        }

        if ($countOfFoundName >= 2) {
            return null;
        }

        $msg = $this->user->username .
            "'s account name from bank is
                    {$bankAccountName} but {$this->user->profile->full_name}   was provided";
        Log::error($msg);
        SendAdminErrorEmailUpdate::dispatch(
            'Failed Withdrawal Attempt',
            $msg
        );
        return $this->sendError(
            null,
            'Account name does not match your registration name. Please contact support to assist.'
        );
    }

    private function checkKYC()
    {
        $totalWithdrawals = $this->user
            ->wallet
            ->transactions()
            ->where('transaction_action', WalletTransactionAction::WinningsWithdrawn)
            ->sum('amount');

        if ($totalWithdrawals >= config('trivia.max_withdrawal_amount') && !$this->user->meta_data['kyc_verified']) {
            $msg = $this->user->username .
                " has reached max withdrawal amount of " .
                config('trivia.max_withdrawal_amount') . " but has not been verified" .
                " total withdrawals so far : {$totalWithdrawals}";
            SendAdminErrorEmailUpdate::dispatch(
                'Failed Withdrawal Attempt',
                $msg
            );
            Log::error($msg);
            return $this->sendError(
                false,
                'Please contact support to verify your identity to proceed with this withdrawal'
            );
        }
    }

    private function cleanName($name)
    {
        $specialChars = array('-', ',');
        return strtoupper(str_replace($specialChars, ' ', trim($name)));
    }
}
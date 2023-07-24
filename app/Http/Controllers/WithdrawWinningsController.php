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
            'account_name' => ['required', 'string']
        ]);

        $totalWithdrawals = $this->user
            ->wallet
            ->transactions()
            ->sum('amount');

        if ($totalWithdrawals >= config('trivia.max_withdrawal_amount') && !$this->identifyVerified($this->user->id)) {
            Log::error($this->user->username . " has reached max withdrawal amount", [
                'totalWithdrawals' => $totalWithdrawals,
                'maxWithdrawalAmount' => config('trivia.max_withdrawal_amount')
            ]);
            return $this->sendError(
                false,
                'Please contact support to verify your identity to proceed with this withdrawal'
            );
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

        $fullName = strtoupper($this->user->profile->first_name . ' ' . $this->user->profile->last_name);

        $verifiedAccountName = $this->getValidAccountName($verifyAccount->data->account_name);

        if (
            ($verifiedAccountName['firstAndLastName'] != strtoupper($fullName))
            && ($verifiedAccountName['lastAndFirstName'] != strtoupper($fullName))
        ) {
            Log::error(
                $this->user->username .
                    "'s possible valid account names are
                {$verifiedAccountName['firstAndLastName']} and {$verifiedAccountName['lastAndFirstName']}
                but {$fullName} was provided"
            );
            SendAdminErrorEmailUpdate::dispatch('Failed Withdrawal Attempt', $this->user->username .
                "'s possible valid account names are
            {$verifiedAccountName['firstAndLastName']} and {$verifiedAccountName['lastAndFirstName']}
            but {$fullName} was provided");
            return $this->sendError(
                null,
                'Account name does not match your registration name. Please contact support to assist.'
            );
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

    private function identifyVerified($userId)
    {
        $verified = [29031959, 29043239];
        return in_array($userId, $verified);
    }
}

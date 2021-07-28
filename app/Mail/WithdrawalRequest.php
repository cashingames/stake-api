<?php

namespace App\Mail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WithdrawalRequest extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $amount;
    public $bankName;
    public $accountName;
    public $accountNumber;

    public function __construct($bankName,$accountName,$accountNumber,$amount)
    {
        //
        $this->amount = $amount;
        $this->bankName = $bankName;
        $this->accountName = $accountName;
        $this->accountNumber = $accountNumber;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->to(config('trivia.admin_withdrawal_request_email'))
        ->subject('REQUEST: CASH WITHDRAWAL REQUEST')
        ->view('emails.users.request');
    }
}

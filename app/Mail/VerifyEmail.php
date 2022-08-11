<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $user;

    public function __construct($user)
    {

        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->to($this->user->email)
            ->from('noreply@cashingames.com')
            ->subject('Verify Your Email')
            ->view('emails.users.verifyEmail')
            ->with([
                'username' => $this->user->username,
                'email' => Crypt::encryptString($this->user->email),
                'year' => Carbon::now()->year,
            ]);
    }
}
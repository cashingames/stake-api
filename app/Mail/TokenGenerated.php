<?php

namespace App\Mail;

use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TokenGenerated extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    private $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $token, User $user)
    {
        $this->token = $token;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->to($this->user->email, $this->user->username)
            ->subject('Cashingame: Reset Password')
            ->view('emails.users.token')
            ->with([
                'username' => $this->user->username,
                'year' => Carbon::now()->year,
            ]);
    }
}

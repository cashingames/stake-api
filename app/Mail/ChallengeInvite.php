<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class ChallengeInvite extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $opponent;
    public $challengeId;

    public function __construct(User $opponent, $challengeId)
    {
        $this->opponent = $opponent;
        $this->challengeId = $challengeId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->to($this->opponent->email)
            ->subject('Cashingames Invitation! : Play a Challenge Game!')
            ->view('emails.users.challengeInvite')
            ->with([
                'opponent' => $this->opponent->username,
                'user' => auth()->user()->username,
                'year' => Carbon::now()->year,
                'challengeId'=>$this->challengeId
            ]);
    }
}

<?php

namespace App\Mail;

use App\Models\Challenge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Carbon\Carbon;

class ChallengeInvite extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $opponent;
    public $challenge;

    public function __construct($opponent, $challenge)
    {   
        
        $this->opponent = $opponent;
        $this->challenge = $challenge;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        
        return $this->to($this->opponent->email)
            ->from('noreply@cashingames.com')
            ->subject('Cashingames Invitation! : Play a Challenge Game!')
            ->view('emails.users.challengeInvite')
            ->with([
                'opponent' => $this->opponent->username,
                'user' => $this->challenge->users->username,
                'year' => Carbon::now()->year,
                'challengeId'=>$this->challenge->id
            ]);
    }
}

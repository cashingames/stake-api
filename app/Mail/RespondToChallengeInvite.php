<?php

namespace App\Mail;

use App\Models\Challenge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Carbon\Carbon;

class RespondToChallengeInvite extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $status;
    public $user;
    public $challengeId;

    public function __construct($status, $user_id, $challengeId)
    {   
        $this->status = $status;
        $this->user = User::find($user_id);
        $this->challengeId = $challengeId;
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
            ->subject('Your Challenge Response !')
            ->view('emails.users.respondToChallengeInvite')
            ->with([
                'opponent' => auth()->user()->username,
                'user' => $this->user->username,
                'year' => Carbon::now()->year,
                'challengeId'=>$this->challengeId,
                'status' => $this->status
            ]);
    }
}

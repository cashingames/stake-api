<?php

namespace App\Mail;

use App\Models\Challenge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Carbon\Carbon;

class RespondToChallengeInvite extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $status;
    public $player;
    public $challengeId;

    public function __construct($status, $player, $challengeId)
    {   
        $this->status = $status;
        $this->player = $player;
        $this->challengeId = $challengeId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $challenge = Challenge::find($this->challengeId);
        return $this->to($this->player->email)
            ->from('noreply@cashingames.com')
            ->subject('Your Challenge Response !')
            ->view('emails.users.respondToChallengeInvite')
            ->with([
                'opponent' => $challenge->opponent->username,
                'user' => $this->player->username,
                'year' => Carbon::now()->year,
                'challengeId'=>$this->challengeId,
                'status' => $this->status
            ]);
    }
}

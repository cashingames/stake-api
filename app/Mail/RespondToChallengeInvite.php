<?php

namespace App\Mail;

use App\Models\Challenge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Mail\Mailables\Address;

class RespondToChallengeInvite extends Mailable
{
    use Queueable, SerializesModels;

    public $status;
    public $player;
    public $challengeId;

    /**
     * Create a new message instance.
     */
    public function __construct($status, $player, $challengeId)
    {   
        $this->status = $status;
        $this->player = $player;
        $this->challengeId = $challengeId;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('noreply@cashingames.com', 'Cashingames'),
            subject: 'Your Challenge Response !',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $challenge = Challenge::find($this->challengeId);
        return new Content(
            view: 'emails.users.respondToChallengeInvite',
            with: [
                'opponent' => $challenge->opponent->username,
                'user' => $this->player->username,
                'year' => Carbon::now()->year,
                'challengeId'=>$this->challengeId,
                'status' => $this->status
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

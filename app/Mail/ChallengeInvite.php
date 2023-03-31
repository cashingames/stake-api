<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Carbon;

class ChallengeInvite extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public $opponent;
    public $challenge;

    public function __construct($opponent, $challenge)
    {
        $this->opponent = $opponent;
        $this->challenge = $challenge;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {

        return new Envelope(
            from: new Address('noreply@cashingames.com', 'Cashingames'),
            subject: 'Cashingames Invitation! : Play a Challenge Game!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.users.challengeInvite',
            with: [
                'opponent' => $this->opponent->username,
                'user' => $this->challenge->users->username,
                'year' => Carbon::now()->year,
                'challengeId' => $this->challenge->id
            ],
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

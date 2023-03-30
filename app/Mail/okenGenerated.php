<?php

namespace App\Mail;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class okenGenerated extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    private $user;
    public $appType;
    /**
     * Create a new message instance.
     */
    public function __construct(string $token, User $user, string $appType)
    {
        $this->token = $token;
        $this->user = $user;
        $this->appType = $appType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
         return new Envelope(
            from: new Address('noreply@thegameark.com', $this->appType),
            subject: "$this->appType: Reset Password",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.users.token',
            with: [
                'username' => $this->user->username,
                'year' => Carbon::now()->year,
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

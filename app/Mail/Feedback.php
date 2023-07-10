<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class Feedback extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public function __construct(
        private readonly string $firstName,
        private readonly string $lastName,
        private readonly string $phone,
        private readonly string $email,
        private readonly string $messageBody,
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('noreply@cashingames.com', 'Cashingames'),
            subject: 'User Feedback',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.feedback',
            with: [
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'phone' => $this->phone,
                'message_body' => $this->messageBody,
                'email' => $this->email,
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

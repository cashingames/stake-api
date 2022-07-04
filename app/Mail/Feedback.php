<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Feedback extends Mailable
{
    use Queueable, SerializesModels;

    public $first_name, $last_name ,$email, $message_body;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($first_name,$last_name,$email,$message_body)
    {
        //
        
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->message_body = $message_body;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {   
        return $this->to(config('app.admin_email'))
        ->from($this->email)
        ->subject('FEEDBACK')
        ->view('emails.feedback')
        ->with([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'message' =>  $this->message_body
        ]);
    }
}

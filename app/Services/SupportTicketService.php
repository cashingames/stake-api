<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


/**
 * Handling support ticket creation.
 */

class SupportTicketService
{
    private $ticketUrl;
    private $ticketKey;

    public function __construct()
    {
        $this->ticketUrl = config('app.osticket_support_url');
        $this->ticketKey = config('app.osticket_support_key');
    }

    public function createTicket($firstName, $lastName, $email, $message, $ip,)
    {
        try {
            Http::withHeaders([
                'X-API-Key' => $this->ticketKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($this->ticketUrl, [
                'name'     =>   $firstName . ' ' . $lastName,
                'email'    =>   $email,
                'subject'   =>  'Inquiry/Complaint',
                'message'   =>  $message,
                'ip'       =>   $ip,
                'topicId'   =>  '1',
                'attachments' => array()
            ]);
        } catch (\Exception $ex) {
            Log::info('ticket could not be created. something went wrong');
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Mail\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Services\SupportTicketService;

class MessagesController extends BaseController
{
    //

    public function feedback(Request $request, SupportTicketService $ticketService)
    {
        $data = $request->validate([
            'first_name' => ['nullable', 'string'],
            'username' => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string'],
            'email' => ['required', 'email', 'string'],
            'message_body' => ['required', 'string']
        ]);

        $firstName = $data["first_name"] ?? '';
        $lastName = $data["last_name"] ?? '';
        $phone = $data["phone_number"] ?? '';


        if ($firstName == '' && $lastName == '' && isset($data["username"]) && !is_null($data["username"])) {
            $firstName = $data["username"];
        }

        Mail::to(config('app.admin_email'))
            ->send(
                new Feedback($firstName, $lastName, $phone, $data["email"], $data["message_body"])
            );

        $ticketService->createTicket(
            $firstName,
            $lastName,
            $phone,
            $data["email"],
            $data["message_body"],
            $request->ip()
        );

        return $this->sendResponse("Feedback Sent", 'Feedback Sent');
    }


    public function fetchFaqAndAnswers()
    {

        $faqs = json_decode(file_get_contents(storage_path() . "/faq.json"), true);

        return $this->sendResponse($faqs, 'data fetched');
    }


    public function fetchFirstTimeBonus()
    {

        $welcomeBonus = json_decode(file_get_contents(storage_path() . "/welcomeBonus.json"), true);

        return $this->sendResponse($welcomeBonus, 'data fetched');
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\ClientPlatform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\Feedback;
use App\Mail\TokenGenerated;
use App\Models\Notification;
use App\Models\User;
use App\Services\SupportTicketService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class MessagesController extends BaseController
{
    //

    public function feedback(Request $request, SupportTicketService $ticketService, ClientPlatform $platform)
    {
        $data = $request->validate([
            'first_name' => ['nullable', 'string'],
            'username' => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string'],
            'email' => ['required', 'email', 'string'],
            'message_body' => ['required', 'string']
        ]);

        $firstName = '';
        $lastName = '';
        $phone = '';

        if (isset($data["first_name"]) && !is_null($data["first_name"])) {
            $firstName =  $data["first_name"];
        }

        if (isset($data["last_name"]) &&  !is_null($data["last_name"])) {
            $lastName =  $data["last_name"];
        }

        if (isset($data["phone_number"]) &&  !is_null($data["phone_number"])) {
            $phone =  $data["phone_number"];
        }

        if($firstName == '' && $lastName == ''){
            if (isset($data["username"]) && !is_null($data["username"])) {
                $firstName = $data["username"];
            }
        }

        $appType = ($platform == ClientPlatform::GameArkMobile) ? "GameArk": "Cashingames";

        Mail::to(config('app.admin_email'))->send(new Feedback($firstName, $lastName, $phone, $data["email"], $data["message_body"], $appType));

        //create automated ticket for support

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

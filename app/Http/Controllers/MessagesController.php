<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\Feedback;
use App\Mail\TokenGenerated;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class MessagesController extends BaseController
{
    //

    public function feedback(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'email' => ['required', 'email', 'string'],
            'message_body' => ['required', 'string']
        ]);

        $firstName = '';
        $lastName = '';

        
        if (isset($data["first_name"]) && !is_null($data["first_name"])) {
            $firstName =  $data["first_name"];
        }

        if (isset($data["last_name"]) &&  !is_null($data["last_name"])) {
            $lastName =  $data["last_name"];
        }

        Mail::send(new Feedback($firstName, $lastName, $data["email"], $data["message_body"]));


        return $this->sendResponse("Feedback Sent", 'Feedback Sent');
    }


    public function fetchFaqAndAnswers()
    {

        $faqs = json_decode(file_get_contents(storage_path() . "/faq.json"), true);

        return $this->sendResponse($faqs, 'data fetched');
    }

    public function fetchNotifications()
    {

        $user = $this->user;

        $notifications = Notification::where('user_id', $user->id)->latest()->limit(20)->get();

        return $this->sendResponse($notifications, 'Recent Notifications');
    }

    public function readNotification($notificationId)
    {

        $notification = Notification::find($notificationId);

        if ($notification === null) {
            return $this->sendError('Notification not found', 'Notification not found');
        }

        $notification->update(['is_read' => true]);

        return $this->sendResponse('message read', 'message read');
    }

    public function readAllNotifications()
    {

        $notifications = Notification::where('user_id', $this->user->id)->get();

        if (count($notifications) === 0) {
            return $this->sendError('No Notifications found', 'No Notifications found');
        }

        foreach ($notifications as $n) {
            $n->update(['is_read' => true]);
        }

        return $this->sendResponse('messages read', 'messages read');
    }

    public function fetchFirstTimeBonus()
    {

        $welcomeBonus = json_decode(file_get_contents(storage_path() . "/welcomeBonus.json"), true);

        return $this->sendResponse($welcomeBonus, 'data fetched');
    }
}

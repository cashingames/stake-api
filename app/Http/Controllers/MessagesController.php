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
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'email' => ['required', 'email', 'string'],
            'message_body' => ['required', 'string']
        ]);


        Mail::send(new Feedback($data["first_name"], $data["last_name"], $data["email"], $data["message_body"]));


        return $this->sendResponse("Feedback Sent", 'Feedback Sent');
    }

    // public function sendInvite(Request $request)
    // {
    //     $data = $request->validate([
    //         'email' => ['required', 'string', 'email']
    //     ]);

    //     $user = User::where('email', $data['email'])->first();
    //     if (!$user) {
    //         return $this->sendError('Email address not found', 'A valid email address was not found');
    //     }

    //     $token = mt_rand(10000, 99999);
    //     Mail::send(new TokenGenerated($token, $user));

    //     // update user's password token and token expiry time
    //     $now = Carbon::now();

    //     $exists = DB::select('select * from password_resets where email = ?', [$data['email']]);

    //     if ($exists) {
    //         DB::table('password_resets')
    //         ->where('email', $data['email'])
    //         ->update(['token' => $token]);
    //     } else {
    //         DB::insert('insert into password_resets (email, token, created_at) values (?, ?, ?)', [$data['email'], $token, $now]);
    //     }

    //     return $this->sendResponse(true, 'Email Sent');
    // }

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
}

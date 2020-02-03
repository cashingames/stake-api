<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use App\Notifications\PasswordResetNotification;
use App\Notifications\DatabaseNotification;


class ForgotPasswordController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    protected function sendResetLinkResponse(Request $request, $response)
    {   
        

        return $this->sendResponse('Link Sent ' , 'Link Sent');
    }

    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return $this->sendError('Link not sent', 'Link not sent');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use App\Notifications\PasswordResetNotification;
use App\Notifications\DatabaseNotification;
use App\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

    // use SendsPasswordResetEmails;

    public function sendEmail(Request $request)
    {   
        $data = $request->validate([
            'email' =>['required', 'string', 'email', 'max:100']
        ]);

        $user = User::where('email', request()->input('email'))->first();

        if (!$user){

            return $this->sendError('Please enter your registered email address', 'Please enter your registered email address');
        }

       
        $token = md5(uniqid(rand(),true));
        
        $mail = new PHPMailer(true);
        $mail->setFrom('noreply@cashingames.com');
        $mail->addAddress($data['email']);
        $mail->isSMTP();
        $mail->Host = "smtp.mailtrap.io";
        $mail->SMTPAuth = true;
        $mail->Username = '8813df984fe70a';
        $mail->Password = 'b4c7e475644605';
        $mail->Subject = 'Reset your password';
        $mail->Body    = "<p>You are recieving this because you requested for a password change.To change your password please use:  $token  </p>";
        $mail->send();

        
        return $this->sendResponse($token, 'Email Sent');
    
    }
}

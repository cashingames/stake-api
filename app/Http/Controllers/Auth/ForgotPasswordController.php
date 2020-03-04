<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use App\Notifications\PasswordResetNotification;
use App\Notifications\DatabaseNotification;
use App\User;
use Illuminate\Support\Carbon;
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
            'email' => ['required', 'string', 'email']
        ]);

        $user = User::where('email', request()->input('email'))->first();

        if (!$user) {

            return $this->sendError('Please enter your registered email address', 'Please enter your registered email address');
        }


        $token = strtoupper(substr(md5(time()), 0, 7));

        $mail = new PHPMailer(true);
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->setFrom('noreply@cashingames.com');
        $mail->addAddress($data['email']);
        $mail->isSMTP();
        $mail->Host = "smtp.mailtrap.io";
        $mail->SMTPAuth = true;
        $mail->Username = 'd4e2142ee174ee';
        $mail->Password = 'cd2095558a4fa7';
        $mail->Subject = 'Reset your password';
        $mail->Body = "You are recieving this because you requested for a password reset.To reset your password please use this code:  $token ";
        $mail->send();

        // update user's password token and token expiry time
        $expiry_time =  Carbon::now()->addMinutes(10);
        $user->password_token = $token;
        $user->token_expiry = $expiry_time;
        $user->save();

        return $this->sendResponse($token, 'Email Sent');
    }


    public function verifyToken(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'token' => ['required', 'string']
        ]);

        if ($data) {
            $user = User::where(['email' => request()->input('email'), 'password_token' => request()->input('token')])->first();

            if (!$user) {
                return $this->sendError('Invalid verification code', 'Invalid verification code');
            }

            $now = Carbon::now();
            if ($now->greaterThan($user->token_expiry)) {
                return $this->sendError('Verification code has expired,  try again later', 'Verification code has expired,  try again later');
            }

            return $this->sendResponse("Verification successful", 'Verification successful');
        } else {
            return $this->sendError('Verification failed', 'verification failed');
        }
    }
}

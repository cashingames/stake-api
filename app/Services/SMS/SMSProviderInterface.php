<?php

namespace App\Services\SMS;

interface SMSProviderInterface{

    public function send(array $data);

    public function deliverOTP($user, $tokenType);
}
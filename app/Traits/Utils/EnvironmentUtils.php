<?php

namespace App\Traits\Utils;

trait EnvironmentUtils
{

    static function setGoogleCredentials(string $env = ""): void
    {
        if (request()->header('x-request-env') == 'development' || $env == 'development') {
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . storage_path('app/firebase/google-credentials-dev.json'));
        } else {
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . storage_path('app/firebase/google-credentials.json'));
        }
    }

}

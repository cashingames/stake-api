<?php

namespace App\Traits\Utils;

trait EnvironmentUtils
{

    static function setGoogleCredentials(): void
    {
        if (request()->header('x-request-env') == 'development') {
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . base_path('google-credentials-dev.json'));
        } else {
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . base_path('google-credentials.json'));
        }
    }

}

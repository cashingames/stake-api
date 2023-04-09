<?php

namespace App\Traits\Utils;

trait EnvironmentUtils
{

    static function setGoogleCredentials(): void
    {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . storage_path('app/firebase/google-credentials.json'));
    }

}

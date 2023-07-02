<?php

namespace App\Traits\Utils;

trait ResolveGoogleCredentials
{

    public function setSpecialGoogleCredentialName($env)
    {
        putenv('GOOGLE_CREDENTIALS_ENV=' . $env);
    }

    public function getGoogleCredentialFileName(?string $header)
    {
        $credentials = 'google-credentials.json';
        if ($header == 'development' || env('GOOGLE_CREDENTIALS_ENV') == 'development') {
            $credentials = 'google-credentials-dev.json';
        } elseif ($header == 'stake-development' || env('GOOGLE_CREDENTIALS_ENV') == 'stake-development') {
            $credentials = 'google-credentials-stake-dev.json';
        } elseif ($header == 'stake-production' || env('GOOGLE_CREDENTIALS_ENV') == 'stake-production') {
            $credentials = 'google-credentials-stake-prod.json';
        } elseif ($header == 'stake-testing' || env('GOOGLE_CREDENTIALS_ENV') == 'stake-testing') {
            $credentials = 'google-credentials-stake-test.json';
        }
        return $credentials;
    }
}

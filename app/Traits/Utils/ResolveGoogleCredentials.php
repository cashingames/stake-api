<?php

namespace App\Traits\Utils;

trait ResolveGoogleCredentials
{

    private static $SPECIALENVS = [
        'development',
        'stake-development',
        'stake-production',
        'stake-testing',
    ];

    public function detectGoogleCredentialEnvironment($env)
    {
        if (in_array($env, self::$SPECIALENVS)) {
            putenv('GOOGLE_CREDENTIALS_ENV=' . ($env));
        }
    }

    public function detectGoogleCredentialName()
    {

        $env = env('GOOGLE_CREDENTIALS_ENV') || request()->header('x-request-env');
        $this->detectGoogleCredentialEnvironment($env);

        $credentials = 'google-credentials.json';
        if ($env == 'development' || env('GOOGLE_CREDENTIALS_ENV') == 'development') {
            $credentials = 'google-credentials-dev.json';
        }
        if ($env == 'stake-development' || env('GOOGLE_CREDENTIALS_ENV') == 'stake-development') {
            $credentials = 'google-credentials-stake-dev.json';
        }
        if ($env == 'stake-production' || env('GOOGLE_CREDENTIALS_ENV') == 'stake-production') {
            $credentials = 'google-credentials-stake-prod.json';
        }
        if ($env == 'stake-testing' || env('GOOGLE_CREDENTIALS_ENV') == 'stake-testing') {
            $credentials = 'google-credentials-stake-test.json';
        }
        return $credentials;
    }
}

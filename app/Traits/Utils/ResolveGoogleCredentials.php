<?php

namespace App\Traits\Utils;

trait ResolveGoogleCredentials
{
    
    public function detectGoogleCredentialEnvironment($env)
    {
        $specialEnv = [
            'development',
            'stake-development',
            'stake-production',
            'stake-testing',
        ];
        
        if (in_array($env, $specialEnv)) {
            putenv('GOOGLE_CREDENTIALS_ENV=' . ($env));
        }
    }

    public function detectGoogleCredentialName($header){
        $credentials = 'google-credentials.json';
        if ($header == 'development' || env('GOOGLE_CREDENTIALS_ENV') == 'development') {
            $credentials = 'google-credentials-dev.json';
        }
        if ($header == 'stake-development' || env('GOOGLE_CREDENTIALS_ENV') == 'stake-development') {
            $credentials = 'google-credentials-stake-dev.json';
        }
        if ($header == 'stake-production' || env('GOOGLE_CREDENTIALS_ENV') == 'stake-production') {
            $credentials = 'google-credentials-stake-prod.json';
        }
        if ($header == 'stake-testing' || env('GOOGLE_CREDENTIALS_ENV') == 'stake-testing') {
            $credentials = 'google-credentials-stake-test.json';
        }
        return $credentials;
    }
}

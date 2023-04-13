<?php

namespace App\Traits\Utils;

trait ResolveGoogleCredentials
{

    public function detectGoogleCredentials($env)
    {
        $specialEnv = [
            'development',
            'stake-development',
            'stake-production'
        ];
        
        if (in_array($env, $specialEnv)) {
            putenv('GOOGLE_CREDENTIALS_ENV=' . ($env));
        }
    }
}

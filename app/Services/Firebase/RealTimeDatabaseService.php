<?php

namespace App\Services\Firebase;

use Kreait\Firebase\Factory;

class RealTimeDatabaseService
{
    public static function connect()
    {
        $firebase = (new Factory)
            ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')))
            ->createFirestore()
            ->database();
        
        return $firebase;
    }
}
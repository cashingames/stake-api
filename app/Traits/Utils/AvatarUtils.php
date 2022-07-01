<?php

namespace App\Traits\Utils;


trait AvatarUtils
{
    private function getAvatarUrl($avatar)
    {
        if ($avatar !== null) {
            return config('app.url') . '/' . $avatar;
        }
        return $avatar;
    }
    
}

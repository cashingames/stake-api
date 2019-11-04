<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [
        /**
         * When using Xdebug to debug your application from a browser, a cookie called "XDEBUG_SESSION" is set.
         * As this cookie was not set, and thus not encrypted, by the Laravel framework, an error will be thrown when the framework
         * automatically detects and tries to decrypt the cookie.
         */
        'XDEBUG_SESSION' //This is to avoid XDEBUG from
    ];
}

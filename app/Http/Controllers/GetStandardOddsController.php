<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GetStandardOddsController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        return $this->sendResponse(config('odds.standard'), 'standard odds fetched');
    }
}

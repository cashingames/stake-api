<?php

namespace App\Http\Controllers;

use App\Models\StandardOdd;
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
        $odds = StandardOdd::active()->orderBy('score', 'DESC')->get();
        return $this->sendResponse($odds, 'standard odds fetched');
    }
}

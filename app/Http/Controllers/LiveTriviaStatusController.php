<?php

namespace App\Http\Controllers;

use App\Models\LiveTrivia;

class LiveTriviaStatusController extends Controller
{
    /**
     * Single action playground
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        return LiveTrivia::active()->first(); //@TODO: return playedStatus for users that have played and status 
    }
}

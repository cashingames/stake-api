<?php

namespace App\Http\Controllers;

use App\Models\LiveTrivia;
use App\Http\ResponseHelpers\LiveTriviaStatusResponse;

class LiveTriviaStatusController extends Controller
{
    /**
     * Single action playground
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $liveTrivia = LiveTrivia::active()->with('contest')->first();

        if($liveTrivia === null){
            return null;
        }

        return (new LiveTriviaStatusResponse())->transform($liveTrivia);
    }
}

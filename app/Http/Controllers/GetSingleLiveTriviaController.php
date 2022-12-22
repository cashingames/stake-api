<?php

namespace App\Http\Controllers;

use App\Http\ResponseHelpers\LiveTriviaStatusResponse;
use App\Models\LiveTrivia;
use Illuminate\Http\Request;

class GetSingleLiveTriviaController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'liveTriviaId' => 'required|exists:trivias,id'
        ]);

        $liveTrivia = LiveTrivia::find($request->liveTriviaId);

        return (new LiveTriviaStatusResponse())->transform($liveTrivia);
        
    }
}

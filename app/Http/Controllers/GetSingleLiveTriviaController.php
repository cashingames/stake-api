<?php

namespace App\Http\Controllers;

use App\Http\ResponseHelpers\LiveTriviaStatusResponse;
use App\Models\LiveTrivia;
use Illuminate\Http\Request;

class GetSingleLiveTriviaController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        

        $liveTrivia = LiveTrivia::find($request->id);
    
        if(is_null($liveTrivia)){
            return $this->sendError('Invalid live trivia', 'Invalid live trivia');
        }

        return (new LiveTriviaStatusResponse())->transform($liveTrivia);
        
    }
}

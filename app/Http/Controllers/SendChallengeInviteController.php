<?php

namespace App\Http\Controllers;

use App\Mail\ChallengeInvite;
use App\Models\Challenge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendChallengeInviteController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {   

        if ($request->has('friendId') && $request->has('categoryId')) {

            $challenge = Challenge::create([
                'category_id' => $request->categoryId,
                'status' => 'PENDING',
                'user_id' => $this->user->id,
                'opponent_id' => $request->friendId
            ]);

            Mail::send(new ChallengeInvite($request->friendId, $challenge->id));

            Log::info("Challenge id : $challenge->id  invite from " . $this->user->username . " sent" );
            
            return $this->sendResponse('Invite Sent', 'Invite Sent');
        }

        return $this->sendError('Friend or category not found', 'Friend or category not found');
    }

}

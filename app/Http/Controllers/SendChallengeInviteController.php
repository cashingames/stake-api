<?php

namespace App\Http\Controllers;

use App\Actions\SendPushNotification;
use App\Mail\ChallengeInvite;
use App\Models\Challenge;
use App\Models\User;
use App\Notifications\ChallengeReceivedNotification;
use App\Services\Firebase\CloudMessagingService;
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

        if (!$request->has('opponentId') || !$request->has('categoryId') ) {
            
            return $this->sendError('Friend or category not found', 'Friend or category not found');

        }

        $challenge = Challenge::create([
            'status' => 'PENDING',
            'user_id' => $this->user->id,
            'category_id' => $request->categoryId,
            'opponent_id' => $request->opponentId
        ]);

        $opponent = User::find($request->opponentId);
        Mail::send(new ChallengeInvite($opponent, $challenge->id));

        
        $pushAction = new SendPushNotification();
        $pushAction->sendChallengeInviteNotification($this->user, $opponent, $challenge);
        
        $opponent->notify(new ChallengeReceivedNotification($challenge, $this->user));

        Log::info("Challenge id : $challenge->id  invite from " . $this->user->username . " sent" );
        
        return $this->sendResponse('Invite Sent', 'Invite Sent');
      
    }

}
